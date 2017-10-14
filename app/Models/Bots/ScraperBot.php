<?php

use Sunra\PhpSimple\HtmlDomParser;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\Exception\UnknownServerException;
use Illuminate\Foundation\Bus\DispatchesJobs;

use App\Jobs\TraderBotExecuteAutoVisit;
use App\Jobs\TraderBotExecuteAutoTrade;
use App\Jobs\TraderBotExecuteAutoTradeObama;
use App\Jobs\TraderBotExecuteCancelOrders;
use App\Jobs\SendTextEmail;

define('PI_CONTEST_OBAMA', 1);

class ScraperBot extends Bot
{
    use DispatchesJobs;
    /**
     * This is the crown jewels.
     * Compare which PiQuestion's value changed the most after a poll update.
     * Update the chance_to_win on the Competition Question.
     * Buy the 1 that improves the most.
     * Short the 1 that falls the most.
     * Beast Mode trade price is average of discountPrice and bestCompetingOffer.
     * Dispatch an Obama Beast Mode trade that will run with #1 priority.
     */
    private function autoTradeObamaQuestions($chances_before_update, $chances_after_update)
    {
        $bump = 0;
        $dump = 0;
        
        foreach ($chances_before_update as $id => $chance_before_update) {
            $obama_question = PiQuestion::find($id);
            $old_chance = $obama_question->chance_to_win;
            $chance_change = $chances_after_update[$id] - $chance_before_update;
            $new_chance = $old_chance + $chance_change;
            $obama_question->chance_to_win = $new_chance;
            $obama_question->save();

            if ($chance_change > $bump) {
                $bump = $chance_change;
                $bumper = $obama_question;
            }
            elseif ($chance_change < $dump) {
                $dump = $chance_change;
                $dumper = $obama_question;
            }
        }
        
        if ($bumper->active && $bumper->auto_trade_me) {
            $job = (new TraderBotExecuteAutoTradeObama($bumper))->onQueue('obamatrades');
            $this->dispatch($job);
        }
        if ($dumper->active && $dumper->auto_trade_me) {
            $job = (new TraderBotExecuteAutoTradeObama($dumper))->onQueue('obamatrades');
            $this->dispatch($job);
        }
    }

    public function cancelCompetitionQuestionOrders($contest_id)
    {
        $contest = PiContest::find($contest_id);
        if ($contest) {
            foreach ($contest->pi_questions as $competitor) {
                if ($competitor->active && $competitor->auto_trade_me) {
                    $job = (new TraderBotExecuteCancelOrders($competitor))->onQueue('canceltrades');
                    $this->dispatch($job);
                }
            }
        }
    }

    public function cancelQuestionOrders($question_id)
    {
        $question = PiQuestion::find($question_id);
        if ($question && $question->active) {
            $job = (new TraderBotExecuteCancelOrders($question))->onQueue('canceltrades');
            $this->dispatch($job);
        }
    }

    /**
     * Find all questions related to this competition. If marked as needing one, dispatch a Visit job.
     */
    public function autoVisitCompetition($contest_id, $delay = 0, $trade = false)
    {
        $contest = PiContest::find($contest_id);
        if ($contest) {
            foreach ($contest->pi_questions as $competitor) {
                if ($competitor->active) {
                    if ($trade && $competitor->auto_trade_me) {
                        $job = (new TraderBotExecuteAutoTrade($competitor))->delay($delay)->onQueue('autotrades');
                    }
                    else {
                        $job = (new TraderBotExecuteAutoVisit($competitor))->delay($delay)->onQueue('autotrades');
                    }
                    $this->dispatch($job);
                }
            }
        }
    }

    /**
     * Dispatch a Visit job for a PiQuestion.
     */
    public function autoVisitQuestion($question_id, $trade = false)
    {
        $question = PiQuestion::find($question_id);
        if ($question && $question->active) {
            if ($trade) {
                $job = (new TraderBotExecuteAutoTrade($question))->onQueue('autotrades');
            }
            else {
                $job = (new TraderBotExecuteAutoVisit($question))->onQueue('autotrades');
            }
            $this->dispatch($job);
        }
    }

    /**
     * Full page is too big for HtmlDomParser, so we just use the relevant table substring.
     * Grabs an evaluation of the Obama markets' valuations and optionally sends to Beast Mode AutoTrade.
     */
    public function scrapeRasObamaDaily($rasmussen)
    {
        $this->makeDriver('http://www.rasmussenreports.com/user/login', 'ScraperBotRasPlatinum');
        Cache::put('ScraperBotRasPlatinum', $this->driver->getSessionID(), 40);
        $this->rasmussenPlatinumLogin();

        $url = 'http://www.rasmussenreports.com/platinum/presidential_page';
        $this->helperGetAUrl($url, 'class', 'ras_prem_blocktop');
        $source = $this->driver->getPageSource();

        $begin_table = strpos($source, 'table class="ras_prem_blocktop"') - 1;
        $end_table = strpos($source, 'div id="prem_intro_block"') - 1;
        $table = substr($source, $begin_table, $end_table - $begin_table);
        $dom = HtmlDomParser::str_get_html($table);

        if ($dom) {
            $tableRow = $dom->find('tr', 1);
            if ($tableRow) {
                $date = $tableRow->find('td', 0);
                if ($date) {
                    $date = date('Y-m-d', strtotime(trim($date->plaintext) . ' -1 day'));
                    $result = (int) trim($tableRow->find('td', 4)->plaintext);
                    $latest_poll = $rasmussen->latest_poll();
                    // OR if the number is different than yesterday's number
                    if ($date > $latest_poll->date_end || $result != $latest_poll->percent_favor) {
                        $this->makeDriver('http://www.google.com', 'AlertMe');

                        $contest = PiContest::find(PI_CONTEST_OBAMA);
                        $chances_before_update = $contest->evaluate();
                        
                        $rasmussen->un_included_actual_result = (int) trim($tableRow->find('td', 4)->plaintext);
                        $rasmussen->new_poll_update_text = 'Ras Daily: ' . $rasmussen->un_included_actual_result;
                        // If today if Friday, also mark this as final for the week
                        if (date('l', strtotime('today')) == 'Friday') {
                            $rasmussen->is_likely_final_for_week = 1;
                        }
                        $rasmussen->save();

                        $result_text = ' -X-X-X-X-X- RASMUSSEN RESULT = ' . $rasmussen->un_included_actual_result;
                        $result_text .= ' -X-X-X-X-X- RASMUSSEN RESULT = ' . $rasmussen->un_included_actual_result;
                        echo $result_text . "\n";
                
                        if ($rasmussen->auto_trade_updates) {
                            // $chances_after_update = $contest->evaluate();
                            // $this->autoTradeObamaQuestions($chances_before_update, $chances_after_update);
                        }
                        else {
                            // $this->autoVisitCompetition(32);
                            // $this->autoVisitCompetition(32, 20);
                        }
                    }
                }
            }
            $dom->clear();
            unset($dom);
            $this->handleScrapeResult($rasmussen);
        }
    }

    /**
     * @todo: there is also a list of tomorrow's polls in the ticker.
     */
    public function scrapeRasRightTrackAndCandidates(RcpContestPollster $rasmussen, $contest)
    {
        $keywords = $contest == 'RightTrack' ? array('Right', 'Direction',) : array('Trump', 'Hillary', 'Clinton', 'Sanders',);

        // Cache::forget('ScraperBotRasPlatinum');
        $this->makeDriver('http://www.rasmussenreports.com/user/login', 'ScraperBotRasPlatinum');
        Cache::put('ScraperBotRasPlatinum', $this->driver->getSessionID(), 40);
        $this->rasmussenPlatinumLogin();

        $this->helperGetAUrl('http://www.rasmussenreports.com/platinum', 'id', 'prem_preview_txt');
        $dom = HtmlDomParser::str_get_html($this->driver->getPageSource());
        if ($dom) {
            $preview_text = $dom->find('div[id=prem_preview_txt] ul', 0);
            // foreach ($preview_text->find('li') as $story) {
                $story = $preview_text->find('li', 1);
                $headline = $story->find('a') ? trim($story->find('a', 0)->plaintext) : '';
                $blurb = $story->find('p') ? trim($story->find('p', 0)->plaintext) : '';
                foreach ($keywords as $key) {
                    if (stristr($headline, $key) && $headline != $rasmussen->last_scrape_title) {
                        $this->makeDriver('http://www.google.com', 'AlertMe');
                        $rasmussen->new_poll_update_text = $headline . "\n\n" . $blurb;
                        $rasmussen->last_scrape_title = $headline;
                        $rasmussen->save();
                    }
                }
            // }
            $dom->clear();
        }

        unset($dom);
        $this->handleScrapeResult($rasmussen);
    }

    /**
     * If we aren't on the login page, assume we're already logged in.
     * @todo: more efficient check for the cboxClose element, or allow it to timeout gracefully.
     */
    private function rasmussenPlatinumLogin()
    {
        if ($this->driver->getCurrentURL() == 'http://www.rasmussenreports.com/user/login') {
            $this->driver->wait()->until(
              WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::id('cboxClose'))
            );
            $this->driver->findElement(WebDriverBy::id('cboxClose'))->click();

            $this->driver->wait()->until(
              WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::id('id1'))
            );

            $this->driver->findElement(WebDriverBy::id('id1'))->sendKeys('robertcharlesfox@gmail.com');
            $this->driver->findElement(WebDriverBy::id('id2'))->sendKeys(env('RAS_PW'));
            $this->driver->findElement(WebDriverBy::id('id3'))->click();
            $this->driver->findElement(WebDriverBy::name('LoginButton'))->click();
            $this->driver->wait()->until(
              WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::id('prem_preview_txt'))
            );
        }
    }

    public function scrapeGallupObama()
    {
        $gallup = RcpContestPollster::where('name', '=', 'Gallup')->where('pi_contest_id', '=', 1)->first();
        if ($gallup->un_included_actual_result > 1) {
            return;
        }

        $url = 'http://www.gallup.com/poll/113980/Gallup-Daily-Obama-Job-Approval.aspx?version=print';
        $scraper = new Scraper($url);
        $dom = HtmlDomParser::str_get_html($scraper->html);

        if ($dom) {
            foreach ($dom->find('table[id=tabulardata] tr') as $tableRow) {
                $isNotHeader = $tableRow->find('td', 0);
                if ($isNotHeader) {
                    $dates = $tableRow->find('td', 0)->plaintext;
                    $dates_prefix = substr($dates, 0, strpos($dates, '/') + 1);
                    $dates_suffix = substr($dates, strpos($dates, '-') + 1);
                    if (substr_count($dates, '/') == 2) {
                        $date = date('Y-m-d', strtotime($dates_prefix . $dates_suffix));
                    }
                    elseif (substr_count($dates, '/') == 3) {
                        $date = date('Y-m-d', strtotime($dates_suffix));
                    }
                    else {
                        return;
                    }

                    if ($date > $gallup->latest_poll()->date_end) {
                        $contest = PiContest::find(1);
                        $chances_before_update = $contest->evaluate();
                        $gallup->un_included_actual_result = $tableRow->find('td', 1)->plaintext;
                        // If today if Friday, also mark this as final for the week
                        if (date('l', strtotime('today')) == 'Friday') {
                            $gallup->is_likely_final_for_week = 1;
                        }
                        $gallup->save();
        
                        if ($gallup->auto_trade_updates) {
                            $chances_after_update = $contest->evaluate();
                            $this->autoTradeObamaQuestions($chances_before_update, $chances_after_update);
                        }
                    }
                }
            }

            $dom->clear();
            unset($dom);
        }
    }

    public function scrapeMonmouth(RcpContestPollster $monmouth)
    {
        $url = 'http://www.monmouth.edu/university/monmouth-university-poll-reports.aspx';
        $scraper = new Scraper($url);
        $dom = HtmlDomParser::str_get_html($scraper->html);

        if ($dom) {
            $table = $dom->find('table', 4);
            if ($table) {
                $link = $table->find('tr td', 1);
                $title = trim($link->plaintext);
                $url = trim($link->find('a', 0)->href);
                if ( ! stristr($title, $monmouth->scrape_instructions)) {
                    // if (stristr($title, 'National')) {
                        $monmouth->new_poll_update_text = "main page link changed \n \n " . $url;
                        $monmouth->selenium_url = $url;
                    // }
                    $monmouth->scrape_instructions = $title;
                    $monmouth->save();
                }
            }
            $dom->clear();
            unset($dom);
            $this->handleScrapeResult($monmouth);
        }
    }

    /**
     * This scrapes for new poll announcements.
     * @todo: once the announcement is there, have it start scraping the announced URL.
     * @todo: could do this in Selenium so that I can see it. Having some text is the key anyway.
     * @todo: no additional trouble to handleScrapeResult if it's in Selenium either.
     */
    public function scrapeQuin(RcpContestPollster $quin)
    {
        // $session_name = 'QuinBot';
        // if (Cache::has($session_name)) {
        //     $session_id = Cache::pull($session_name);
        //     $this->driver = RemoteWebDriver::createBySessionID($session_id, 'http://127.0.0.1:4445/wd/hub');
        // }
        // else {
        //     $capabilities = array(WebDriverCapabilityType::BROWSER_NAME => 'chrome');
        //     $this->driver = RemoteWebDriver::create('http://127.0.0.1:4445/wd/hub', $capabilities);
        // }
        // // Open a new tab
        // // $this->driver->getKeyboard()->sendKeys(array(WebDriverKeys::COMMAND, 't'));

        // $url = 'http://www.quinnipiac.edu/news-and-events/quinnipiac-university-poll/national/release-detail?ReleaseID=2321';
        // $this->driver->get($url);            

        // Cache::put($session_name, $this->driver->getSessionID(), 5);
        // return;

        $url = 'http://www.quinnipiac.edu/news-and-events/quinnipiac-university-poll/';
        $scraper = new Scraper($url);
        $dom = HtmlDomParser::str_get_html($scraper->html);

        $releases = $dom->find('article[class=mainColumn] p');
        $releaseIdComparator = 0;
        foreach ($releases as $release) {
            $fullTopline = $release->plaintext;
            $link = $release->find('a', 0);
            if ($link) {
                $linkDescription = $link->plaintext;
                $releaseUrl = $link->href;
                $releaseId = substr($releaseUrl, strpos($releaseUrl, 'ID=') + 3);
                if ($releaseId > $quin->last_scrape_link) {
                    // This poll has a higher ID than the last poll of interest.
                    // It's a new poll announcement.
                    if (stristr($fullTopline, 'Results of a')) {
                        if ($quin->last_scrape_other != 'announcement') {
                            $quin->last_scrape_other = 'announcement';
                            $quin->selenium_url = $releaseUrl;
                            $quin->save();
                            // By not saving the update text, it will continue scraping again.
                            $quin->new_poll_update_text = "new Poll coming \n \n " . $fullTopline;
                        }
                    }
                    // It's an actual poll.
                    else {
                        $quin->new_poll_update_text = "new Poll is out \n \n " . $releaseUrl;
                        $quin->last_scrape_title = $fullTopline;
                        $quin->last_scrape_link = $releaseId;
                        $quin->last_scrape_other = '';
                        $quin->selenium_url = $releaseUrl;
                        $quin->save();
                    }
                    
                    // There are gaps in the numbering system.
                    // if (($releaseId + 1) < $releaseIdComparator) {
                    //     if ($quin->last_scrape_other != 'announcement' && $quin->last_scrape_other != 'gaps') {
                    //         $quin->last_scrape_other = 'gaps';
                    //         $quin->save();
                    //         // By not saving the update text, it will continue scraping again.
                    //         $quin->new_poll_update_text = "gaps in Quin poll list";
                    //     }
                    // }
                    // $releaseIdComparator = $releaseId;
                }
            }
        }

        $dom->clear();
        unset($dom);
        $this->handleScrapeResult($quin);
    }

    public function scrapePew(RcpContestPollster $pew)
    {
        $url = 'http://www.people-press.org/category/publications/';
        $scraper = new Scraper($url);
        $dom = HtmlDomParser::str_get_html($scraper->html);

        if ($dom) {
            $link = $dom->find('div[id=content]', 0)->find('a', 0);
            if ($link) {
                $title = trim($link->find('h2', 0)->plaintext);
                $url = trim($link->href);
                if ( ! stristr($title, $pew->scrape_instructions)) {
                    $pew->new_poll_update_text = "title changed \n \n " . $url;
                    $pew->selenium_url = $url;
                    $pew->scrape_instructions = $title;
                    $pew->save();
                }
            }

            $dom->clear();
            unset($dom);
            $this->handleScrapeResult($pew);
        }
    }

    public function scrapePewForum(RcpContestPollster $pew)
    {
        $url = 'http://www.pewforum.org/category/publications/';
        $scraper = new Scraper($url);
        $dom = HtmlDomParser::str_get_html($scraper->html);

        if ($dom) {
            $link = $dom->find('div[id=content]', 0)->find('a', 0);
            if ($link) {
                $title = trim($link->find('h2', 0)->plaintext);
                $url = trim($link->href);
                if ( ! stristr($title, $pew->scrape_instructions)) {
                    $pew->new_poll_update_text = "title changed \n \n " . $url;
                    $pew->selenium_url = $url;
                    $pew->scrape_instructions = $title;
                    $pew->save();
                }
            }

            $dom->clear();
            unset($dom);
            $this->handleScrapeResult($pew);
        }
    }

    public function scrapeAp(RcpContestPollster $ap)
    {
        $url = 'http://ap-gfkpoll.com/';
        $scraper = new Scraper($url);
        $dom = HtmlDomParser::str_get_html($scraper->html);

        $headline = $dom->find('div[id=headline]', 0);
        if ($headline) {
            $title = $headline->find('div[id=headlinetitle]', 0);
            if ($title) {
                if ( ! stristr($title->plaintext, $ap->scrape_instructions)) {
                    $toplineButton = $headline->find('div[id=headlinebtns] span[id=headlinedl]', 0);
                    if ($toplineButton) {
                        $url = trim($headline->find('div[id=headlinebtns] a', 0)->href);
                    }
                    else {
                        $url = trim($title->href);
                    }
                    $ap->new_poll_update_text = "headline link changed \n \n " . $url;
                    $ap->selenium_url = $url;
                    $ap->scrape_instructions = $title->plaintext;
                    $ap->save();
                }
            }
        }
        $dom->clear();
        unset($dom);
        $this->handleScrapeResult($ap);
    }

    public function scrapeSuffolk(RcpContestPollster $suffolk)
    {
        $url = 'http://www.suffolk.edu/academics/10741.php';
        $scraper = new Scraper($url);
        $dom = HtmlDomParser::str_get_html($scraper->html);

        if ($dom) {
            $latest_report = $dom->find('div[id=tab_control0] div[name=tabbed_item]', 0);
            if ($latest_report) {
                $headline = $latest_report->find('h3', 0);
                if ($headline) {
                    // Title is plaintext of headline. Marginals URL is in one of the <p> tags. Need to know both.
                    $title = trim($headline->plaintext);
                    $marginals_link = '';
                    foreach ($latest_report->find('p') as $p) {
                        if (stristr($p->plaintext, 'Marginals') && $p->find('a', 0)) {
                            $marginals_link = 'https://www.suffolk.edu' . $p->find('a', 0)->href;
                        }
                    }

                    // First check if the title / headline date has changed.
                    if ( ! stristr($title, $suffolk->scrape_instructions)) {
                        $suffolk->new_poll_update_text = "headline changed \n \n " . $url;
                        $suffolk->scrape_instructions = $title;
                        $suffolk->url_for_scraping = $marginals_link;
                        $suffolk->selenium_url = $url;
                        $suffolk->save();
                    }
                    // Then check if there is a new link to updated Marginals.
                    else {
                        if ( ! stristr($marginals_link, $suffolk->url_for_scraping)) {
                            $suffolk->new_poll_update_text = "marginals changed \n \n " . $marginals_link;
                            $suffolk->url_for_scraping = $marginals_link;
                            $suffolk->selenium_url = $marginals_link;
                            $suffolk->save();
                        }
                    }
                }
            }
            $dom->clear();
            unset($dom);
            $this->handleScrapeResult($suffolk);
        }
    }

    /**
     * @todo: make a Dom and find the 'Poll' element. Find better identifying attributes than strpos.
     */
    // $suffolk->last_scrape_date = '';
    // $suffolk->last_scrape_title = '';
    // $suffolk->last_scrape_size = '';
    // $suffolk->last_scrape_link = '';
    public function scrapeSuffolkUsat(RcpContestPollster $suffolk)
    {
        $url = 'http://www.usatoday.com/news/politics/';
        $scraper = new Scraper($url);
        $html = $scraper->html;
        if (stristr($html, 'Poll')) {
            $poll_location = strpos($html, 'Poll');
            if ($poll_location != $suffolk->last_scrape_other) {
                $suffolk->last_scrape_other = $poll_location;
                $suffolk->new_poll_update_text = "Poll keyword found \n \n " . $url;
                $suffolk->selenium_url = $url;
                $suffolk->save();
            }
        }
        $this->handleScrapeResult($suffolk, 'Poll');
    }

    public function scrapeGallupHomePage($gallup, $search_term)
    {
        $url = 'http://www.gallup.com/home.aspx';
        $scraper = new Scraper($url);
        $html = $scraper->html;
        if (stristr($html, $search_term)) {
            // if (substr_count($html, $search_term) > 0) {
            if (substr_count($html, $search_term) != 2) {
                $gallup->new_poll_update_text = 'search term matches: ' . $search_term;
                $gallup->selenium_url = $url;
                $gallup->save();
            }
        }
        $this->handleScrapeResult($gallup, $search_term);
    }

    public function scrapeGallupCongressImage($gallup)
    {
        $url = 'http://www.gallup.com/poll/1600/congress-public.aspx?version=print';
        $scraper = new Scraper($url);
        $dom = HtmlDomParser::str_get_html($scraper->html);
        if ($dom) {
            $table = $dom->find('div[class=article-content] p', 1);
            $image = $table->find('img[src]', 0);
            if ( ! $image || ! $image->src || $image->src != $gallup->url_for_scraping) {
                $gallup->new_poll_update_text = ($image && $image->src) ? 'http:' . $image->src : 'page changed';
                $gallup->url_for_scraping = ($image && $image->src) ? $image->src : 'page changed';
                $gallup->selenium_url = ($image && $image->src) ? 'http:' . $image->src : '';
                $gallup->save();
            }
            else {
                $url = 'http:' . $image->src;
                $img = public_path() . '/img/gallup.png';
                file_put_contents($img, file_get_contents($url));
                $size = filesize($img);
                if ($size != $gallup->scrape_instructions) {
                    $gallup->new_poll_update_text = "image size changed \n \n http:" . $image->src;
                    $gallup->scrape_instructions = $size;
                    $gallup->selenium_url = "http:" . $image->src;
                    $gallup->save();
                }
            }
            $dom->clear();
            unset($dom);
            $this->handleScrapeResult($gallup, 'Congress');
        }
    }

    public function scrapeReutersWeeklyReport(RcpContestPollster $reuters)
    {
        $url = 'http://www.ipsos-na.com/news-polls/reuters-polls/';
        $scraper = new Scraper($url);

        $allHtml = $scraper->html;
        $loc_begin_parse = strpos($allHtml, '/news-polls/pressrelease.aspx?id=7023');
        $loc_end_parse = strpos($allHtml, '/news-polls/pressrelease.aspx?id=4931');
        $beginning = substr($allHtml, 0, $loc_begin_parse);
        $end = substr($allHtml, $loc_end_parse);

        $scraper->html = $beginning . $end;
        $dom = HtmlDomParser::str_get_html($scraper->html);

        for ($i=0; $i < 3; $i++) { 
            $link = $dom->find('div[id=library] h2 a', $i);
            $title = trim($link->plaintext);
            $url = 'http://www.ipsos-na.com' . trim($link->href);
            if (stristr($title, 'Core Political')) {
                if ( ! stristr($url, $reuters->url_for_scraping)) {
                    $headline = $dom->find('div[id=library] p', (($i * 2) + 1));
                    if ($headline) {
                        echo $headline->plaintext;
                    }
                    $seleniumUrl = $url;
                    $reuters->new_poll_update_text = "report link changed \n \n " . $seleniumUrl;
                    $reuters->url_for_scraping = $url;
                    $reuters->selenium_url = $seleniumUrl;
                    $reuters->save();

                    // $toplineScraper = new Scraper($url);
                    // $toplineDom = HtmlDomParser::str_get_html($toplineScraper->html);
                    // $topline = $toplineDom->find('div[id=contents] p a', 0);
                    // $seleniumUrl = $topline ? 'http://www.ipsos-na.com' . trim($topline->href) : $url;
                    // $toplineDom->clear();
                    // unset($toplineDom);
                }
                break;
            }
        }

        $dom->clear();
        unset($dom);
        $this->handleScrapeResult($reuters);
    }

    public function scrapeReutersWeeklyUpdate(RcpContestPollster $reuters, $r_id, $search_text, $collapsed)
    {
        $this->makeDriver('http://polling.reuters.com/#!poll/CP' . $r_id, 'ScraperBotReutersUpdate', false, true, 1100);
        $this->driver->wait()->until(
          WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::id('labelList'))
        );

        $dom = HtmlDomParser::str_get_html($this->driver->getPageSource());
        $results = $dom->find('div[id=labels]', 0);
        $end_date = $results->find('h3[id=date]', 0)->plaintext;

        if ($end_date != $reuters->scrape_instructions) {
            $new_result = $results->find('ul[id=labelList] li', 1);
            $new_result = $new_result->find('span', 1)->plaintext;
            $new_result = str_replace('%', '', $new_result);
            $reuters->scrape_instructions = $end_date;
            $reuters->new_poll_update_text = $new_result . ' on ' . $end_date;
            $reuters->projected_result = $new_result;
            $reuters->save();
        }
        $dom->clear();
        unset($dom);

        Cache::put('ScraperBotReutersUpdate', $this->driver->getSessionID(), 10);
    }

    public function scrapeEconomistWeekly(RcpContestPollster $econ)
    {
        $url = 'https://today.yougov.com/publicopinion/archive/?year=&month=&category=economist';
        $scraper = new Scraper($url);
        $dom = HtmlDomParser::str_get_html($scraper->html);

        if ($dom) {
            $link = $dom->find('table[class=archive-table] tbody tr td a', 0);
            if ( ! stristr($link->plaintext, $econ->scrape_instructions)) {
                $econ->new_poll_update_text = "report title changed \n \n http:" . $link->href;
                $econ->selenium_url = 'http:' . $link->href;
                $econ->scrape_instructions = trim($link->plaintext);
                $econ->save();
            }
            $dom->clear();
            unset($dom);
            $this->handleScrapeResult($econ);
        }
    }

    public function scrapeCbs(RcpContestPollster $cbs)
    {
        $url = 'http://www.cbsnews.com/feature/cbs-news-polls/';
        $scraper = new Scraper($url);
        $dom = HtmlDomParser::str_get_html($scraper->html);

        $link = $dom->find('div[class=listing-basic-lead] ul li a', 0);
        if ($link) {
            $url = 'http://www.cbsnews.com' . $link->href;
            $dateline = $link->find('span[class=date]', 0)->plaintext;
            if ($dateline != $cbs->last_scrape_date) {
                $cbs->new_poll_update_text = "report dateline changed \n \n " . $url;
                $cbs->last_scrape_date = $dateline;
                $cbs->last_scrape_link = $url;
                $cbs->last_scrape_title = trim($link->plaintext);
                $cbs->selenium_url = $url;
                $cbs->save();
            }
        }
        $dom->clear();
        unset($dom);
        $this->handleScrapeResult($cbs);
    }

    public function scrapeNationalJournal(RcpContestPollster $nj)
    {
        $url = 'http://heartlandmonitor.com/category/poll-results/';
        $scraper = new Scraper($url);
        $dom = HtmlDomParser::str_get_html($scraper->html);

        if ($dom) {
            $story_section = $dom->find('div[class=section-body]', 0);
            if ($story_section) {
                $top_story = $story_section->find('a[class=story-item]', 0);
                if ($top_story && $top_story->href != $nj->last_scrape_link) {
                    $top_story_link = trim($top_story->href);
                    $nj->new_poll_update_text = "top story changed \n \n " . $top_story_link;
                    $nj->last_scrape_link = $top_story_link;
                    $nj->selenium_url = $top_story_link;
                    $nj->save();
                }
            }
            $dom->clear();
            unset($dom);
            $this->handleScrapeResult($nj);
        }
    }

    public function scrapeIbd(RcpContestPollster $ibd)
    {
        $url = 'http://news.investors.com/editorials/polls.htm';
        $scraper = new Scraper($url);
        $dom = HtmlDomParser::str_get_html($scraper->html);

        $story_section = $dom ? $dom->find('div[id=main]', 0) : false;
        if ($story_section) {
            $top_story = $story_section->find('div h2[class=am-title] a', 0);
            $this->standardPollsterHtmlComparison($ibd, $top_story);
            $dom->clear();
        }
        unset($dom);
        $this->handleScrapeResult($ibd);
    }

    /**
     * First need to parse the page HTML, it is too big for HtmlDomParser.
     * Just slice a bunch of polls out of the middle, identifying them with their href.
     */
    public function scrapeMarist(RcpContestPollster $marist)
    {
        $url = 'http://maristpoll.marist.edu/politics-section/national-poll-archive/';
        $scraper = new Scraper($url);

        $allHtml = $scraper->html;
        $loc_begin_parse = strpos($allHtml, 'http://maristpoll.marist.edu/1112-what-matters-in-a-presidential-candidate/');
        $loc_end_parse = strpos($allHtml, 'http://maristpoll.marist.edu/national-poll-campaign-2004-the-candidates-and-the-agenda/');
        $beginning = substr($allHtml, 0, $loc_begin_parse);
        $end = substr($allHtml, $loc_end_parse);
        $scraper->html = $beginning . $end;

        $dom = HtmlDomParser::str_get_html($scraper->html);
        $story_section = $dom->find('div[id=contentleft]', 0);
        if ($story_section) {
            $top_story = $story_section->find('div h1 a', 0);
            $this->standardPollsterHtmlComparison($marist, $top_story);
        }
        $dom->clear();
        unset($dom);
        $this->handleScrapeResult($marist);
    }

    public function scrapeBloomberg(RcpContestPollster $bloomberg)
    {
        $url = 'https://www.scribd.com/user/267425383/bloombergpolitics';
        $scraper = new Scraper($url);
        // Let it sleep to make sure the page fully loads and the poll count is correct?
        sleep(2);
        $dom = HtmlDomParser::str_get_html($scraper->html);

        $published = $dom->find('div[class=stats_container] a[class=published] div[class=number]', 0);
        $story_section = $dom->find('div[class=documents]', 0);

        if ($published) {
            $current_story_count = (int) $published->plaintext;
            $old_story_count = (int) $bloomberg->last_scrape_other;
            if ($current_story_count > $old_story_count) {
                $bloomberg->new_poll_update_text = "new poll count \n \n " . $current_story_count;
                $bloomberg->last_scrape_other = $current_story_count;
                $bloomberg->selenium_url = $url;
                $bloomberg->save();
            }
        }

        // Actually getting the poll is more important than the count.
        if ($story_section) {
            $top_story_title = trim($story_section->find('div[class=under_title]', 0)->plaintext);
            $top_story_link = trim($story_section->find('a[class=doc_link]', 0)->href);
            if ($top_story_title != $bloomberg->last_scrape_title) {
                $bloomberg->new_poll_update_text = "new poll \n \n " . $top_story_title . '  ' . $top_story_link;
                $bloomberg->last_scrape_title = $top_story_title;
                $bloomberg->selenium_url = $top_story_link;
                $bloomberg->save();
            }
        }
        $dom->clear();
        unset($dom);
        $this->handleScrapeResult($bloomberg);
    }

    public function scrapeMtp()
    {
        $url = 'http://www.nbcnews.com/meet-the-press';
        $scraper = new Scraper($url);
    }

    public function scrapeFoxPolls(RcpContestPollster $fox)
    {
        $url = 'http://www.foxnews.com/official-polls/index.html';
        $this->makeDriver($url, 'ScraperBotFox', false, true);
        $this->driver->wait()->until(
          WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::id('polls'))
        );
        sleep(1);
        Cache::put('ScraperBotFox', $this->driver->getSessionID(), 30);

        $dom = HtmlDomParser::str_get_html($this->driver->getPageSource());
        $story_section = $dom->find('div[id=polls]', 0);
        // There are a bunch of polls here. They shuffle them sometimes...
        if ($story_section) {
            $top_story = $story_section->find('ul li h3 a', 0);
            $this->standardPollsterHtmlComparison($fox, $top_story);
        }
        $dom->clear();
        unset($dom);
        $this->handleScrapeResult($fox);
    }

    /**
     * Gets JSON response via the HuffPo API.
     * Check if it's in the database already, and if not, add it.
     * Pull info out of the object and turn it into an info blurb.
     */
    public function scrapeHuffPo()
    {
        $topics = array('2016-president', '2016-senate', '2016-house', 'obama-job-approval',);
        foreach ($topics as $topic) {
            $url = 'http://elections.huffingtonpost.com/pollster/api/polls.json?topic=' . $topic;
            $scraper = new Scraper($url);
            $results = json_decode($scraper->html);
            if ($results) {
                foreach ($results as $poll) {
                    $id = $poll->id;
                    if ( ! HuffpoPoll::where('huffpo_id', '=', $id)->where('topic', '=', $topic)->count()) {
                        foreach ($poll->questions as $question) {
                            if ($question->topic == $topic) {
                                $info = $poll->pollster . ' - ' . $question->name . PHP_EOL;
                                $info .= $poll->start_date . ' - ' . $poll->end_date . PHP_EOL;
                                $answers = array();
                                $combined_answers = array();
                                foreach ($question->subpopulations[0]->responses as $candidate) {
                                    $answers[$candidate->choice] = $candidate->value;
                                }
                                arsort($answers);
                                foreach ($answers as $key => $value) {
                                    $combined_answers[] = $key . ' ' . $value;
                                }
                                $info .= implode(PHP_EOL, $combined_answers);

                                $p = new HuffpoPoll();
                                $p->huffpo_id = $id;
                                $p->pollster = $poll->pollster;
                                $p->start_date = $poll->start_date;
                                $p->end_date = $poll->end_date;
                                $p->topic = $topic;
                                $p->result_text = $info;
                                $p->result_json = json_encode($poll);
                                $p->save();

                                $job = (new SendTextEmail('huffpo@mm.dev', 'New HuffpoPoll', $info))->onQueue('texts');
                                $this->dispatch($job);

                                echo $info . PHP_EOL;
                            }
                        }
                    }
                }
            }
        }
    }

    public function standardPollsterHtmlComparison(RcpContestPollster $pollster, $top_story)
    {
        if ($top_story && $top_story->href != $pollster->last_scrape_link) {
            $top_story_link = trim($top_story->href);
            $top_story_title = trim($top_story->plaintext);
            $pollster->new_poll_update_text = "top story changed \n \n " . $top_story_title . ' ' . $top_story_link;
            $pollster->last_scrape_link = $top_story_link;
            $pollster->last_scrape_title = $top_story_title;
            $pollster->selenium_url = $top_story_link;
            $pollster->save();
        }
    }

    public function handleScrapeResult(RcpContestPollster $pollster, $news = '', $evaluate = false) {
        $time = date('l m/d H:i:s', strtotime('now'));

        if (strlen($pollster->new_poll_update_text) > 2) {
            echo $time . ' Report Found for ' . $pollster->name . ': ' . $pollster->new_poll_update_text . "\n";

            if (strlen($pollster->selenium_url) > 2) {
                Cache::put('UpdateLocation', $pollster->selenium_url, 15);
                $this->keepUpdateBotWarm();
                // $this->makeDriver($pollster->selenium_url);
            }

            // Send text email to me with the news.
            $from = 'no@mm.dev';
            $subject = 'New ' . $pollster->name . ' ' . $news;
            $body = $pollster->new_poll_update_text;
            $job = (new SendTextEmail($from, $subject, $body))->onQueue('texts');
            $this->dispatch($job);

            if ($evaluate) {
                $pollster->pi_contest->evaluate();
            }

            // Clear a field so I don't get a million text messages.
            if ($pollster->keep_scraping) {
                $pollster->new_poll_update_text = '';
                $pollster->save();
            }
        }
        else {
            echo $time . ' Scrape complete, no news for ' . $pollster->name . "\n";
        }
    }

    /**
     * Make sure we have a Selenium session ready and waiting in the Cache. If not, create one.
     * @todo: figure out try/catch for when the server can't find the session.
     * @todo: not sure if this should be handled here or in Laravel error area.
     */
    public function keepUpdateBotWarm($update_location = '')
    {
        return;
        $session_name = 'UpdateBot';
        // Cache::forget($session_name);
        if (Cache::has($session_name)) {
            $session_id = Cache::pull($session_name);
            $this->update_driver = RemoteWebDriver::createBySessionID($session_id, 'http://127.0.0.1:4445/wd/hub');
        }
        else {
            $capabilities = array(WebDriverCapabilityType::BROWSER_NAME => 'chrome');
            $this->update_driver = RemoteWebDriver::create('http://127.0.0.1:4445/wd/hub', $capabilities);
        }
        Cache::put($session_name, $this->update_driver->getSessionID(), 25);

        if (Cache::has('UpdateLocation') || $update_location) {
            $update_location = $update_location ? $update_location : Cache::get('UpdateLocation');
            $this->update_driver->get($update_location);

            $dim = new WebDriverDimension(1000, 1000);
            try {
                $this->update_driver->manage()->window()->setSize($dim);
            } catch (UnknownServerException $e) {
                Log::info('Line 964 Unknown Server, WTF? ' . $e->getMessage() . $update_location);
                print "Unknown Server" . PHP_EOL;
            }
        }
        else {
            $dim = new WebDriverDimension(100, 100);
            try {
                $this->update_driver->manage()->window()->setSize($dim);
            } catch (UnknownServerException $e) {
                Log::info('Line 974 Unknown Server, WTF? ' . $e->getMessage() . $update_location);
                print "Unknown Server" . PHP_EOL;
            }
            $this->update_driver->get('http://www.google.com');
        }
        sleep(1);
        Cache::put($session_name, $this->update_driver->getSessionID(), 25);
    }

    public function scrapeOpinionSavvy(RcpContestPollster $os)
    {
        $url = 'http://opinionsavvy.com/wp-content/uploads/2016/10/';
        $scraper = new Scraper($url);
        $size = strlen($scraper->html);
        if ($size != $os->last_scrape_size) {
            $os->new_poll_update_text = "page size changed \n \n " . $url;
            $os->selenium_url = $url;
            $os->last_scrape_size = $size;
            $os->save();
            $this->handleScrapeResult($os);
        }
    }
}

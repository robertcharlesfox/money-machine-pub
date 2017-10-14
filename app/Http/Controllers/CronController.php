<?php

use App\Jobs\ScrapeHuffPo;
use App\Jobs\ScrapeAp;
use App\Jobs\ScrapeBloomberg;
use App\Jobs\ScrapeCbs;
use App\Jobs\ScrapeEconomist;
use App\Jobs\ScrapeFoxPolls;
use App\Jobs\ScrapeGallupCongressHomePage;
use App\Jobs\ScrapeGallupCongressImage;
use App\Jobs\ScrapeIbd;
use App\Jobs\ScrapeMarist;
use App\Jobs\ScrapeMonmouth;
use App\Jobs\ScrapeMtp;
use App\Jobs\ScrapeNationalJournal;
use App\Jobs\ScrapePew;
use App\Jobs\ScrapePewForum;
use App\Jobs\ScrapeQuin;
use App\Jobs\ScrapeRasCandidates;
use App\Jobs\ScrapeRasObamaDaily;
use App\Jobs\ScrapeRasRightTrack;
use App\Jobs\ScrapeReutersWeekly;
use App\Jobs\ScrapeSuffolk;
use App\Jobs\ScrapeSuffolkUsat;
use App\Jobs\SendTextEmail;
use App\Jobs\Pollsters\ScrapeMonmouthPDF;

use PredictIt\Trader;
use PredictIt\Navigator;

class CronController extends Controller {

    public function pollsterNeedsScraping(RcpContestPollster $pollster) {
        if (strlen($pollster->new_poll_update_text) < 3 && ! $pollster->is_likely_final_for_week) {
            return true;
        }
        return false;
    }

    public function handleResult(RcpContestPollster $pollster, $news = '', $evaluate = false) {
        if (strlen($pollster->new_poll_update_text) > 2) {
            $from = 'no@mm.dev';
            $subject = 'New ' . $pollster->name . ' ' . $news;
            $body = $pollster->new_poll_update_text;
            $job = (new SendTextEmail($from, $subject, $body))->onQueue('texts');
            $this->dispatch($job);

            if (strlen($pollster->selenium_url) > 2) {
                $bot = new ScraperBot();
                $bot->makeDriver($pollster->selenium_url);
            }
            if ($evaluate) {
                $pollster->pi_contest->evaluate();
            }
        }
    }

    /**
     * Scrapes polling data from realclearpolitics.com
     */
    public function getRcpPolls($scrape_frequency)
    {       
        $rcp_contests = PiContest::where('category', '=', 'poll_rcp')
            ->where('rcp_scrape_frequency', '=', $scrape_frequency)
            ->get()
        ;

        foreach ($rcp_contests as $contest) {
            $scrape = new RcpScrape();
            $scrape->scrape($contest);
            if ($scrape->has_change_since_last_scrape) {
                // $contest->checkForDropTrades();
                if ($contest->rcp_update_txt_alert) {
                    $from = 'no@mm.dev';
                    $subject = $scrape->average;
                    $body = $contest->name . ': ' . $scrape->update_text;
                    $job = (new SendTextEmail($from, $subject, $body))->onQueue('texts');
                    $this->dispatch($job);

                    $bot = new ScraperBot();
                    $bot->keepUpdateBotWarm($contest->url_of_answer);
                    Cache::put('UpdateLocation', $contest->url_of_answer, 15);
                }
            }
        }
        
        return;
    }

    public function getGallupObama()
    {
        $bot = new ScraperBot();
        $bot->scrapeGallupObama();
    }

    public function scrapeMonmouthPDF()
    {
        $scrapes_per_minute = 1;
        for ($i=0; $i < $scrapes_per_minute; $i++) { 
            $delay = (int) ((60 / $scrapes_per_minute) * $i);
            $job = (new ScrapeMonmouthPDF())->delay($delay);
            $this->dispatch($job);
        }
    }

    public function getRasmussenObama()
    {
        $rasmussen = RcpContestPollster::where('pi_contest_id', '=', 1)->where('name', '=', 'Rasmussen')->first();
        if ($this->pollsterNeedsScraping($rasmussen)) {
            $scrapes_per_minute = 1;
            for ($i=0; $i < $scrapes_per_minute; $i++) { 
                $delay = (int) ((60 / $scrapes_per_minute) * $i);
                $job = (new ScrapeRasObamaDaily($rasmussen))->delay($delay);
                $this->dispatch($job);
            }
        }
    }

    public function scrapeOpinionSavvy()
    {
        $bot = new ScraperBot();
        $os = RcpContestPollster::where('pi_contest_id', '=', 225)->where('name', '=', 'Opinion Savvy')->first();
        $bot->scrapeOpinionSavvy($os);
    }

    public function getRasmussenRightTrack()
    {
        $rasmussen = RcpContestPollster::where('pi_contest_id', '=', 3)->where('name', '=', 'Rasmussen')->first();
        if ($this->pollsterNeedsScraping($rasmussen)) {
            $scrapes_per_minute = 1;
            for ($i=0; $i < $scrapes_per_minute; $i++) { 
                $delay = (int) ((60 / $scrapes_per_minute) * $i);
                $job = (new ScrapeRasRightTrack($rasmussen))->delay($delay);
                $this->dispatch($job);
            }
        }
    }

    public function getRasmussenCandidates()
    {
        $rasmussen = RcpContestPollster::where('pi_contest_id', '=', 12)->where('name', '=', 'Rasmussen')->first();
        if (strlen($rasmussen->new_poll_update_text) < 3 && $rasmussen->keep_scraping) {
            $scrapes_per_minute = 1;
            for ($i=0; $i < $scrapes_per_minute; $i++) { 
                $delay = (int) ((60 / $scrapes_per_minute) * $i);
                $job = (new ScrapeRasCandidates($rasmussen))->delay($delay);
                $this->dispatch($job);
            }
        }
    }

    public function getGallupCongress()
    {
        $gallup = RcpContestPollster::where('pi_contest_id', '=', 8)->where('name', '=', 'Gallup')->first();
        if ($this->pollsterNeedsScraping($gallup)) {
            $scrapes_per_minute = 4;
            for ($i=0; $i < $scrapes_per_minute; $i++) { 
                $delay = (int) ((60 / $scrapes_per_minute) * $i);
                $job = (new ScrapeGallupCongressImage($gallup))->delay($delay);
                $this->dispatch($job);
            }
        }
    }

    /**
     * Can refactor this later to accept multiple markets/search terms if needed. For now just Congress.
     */
    public function getGallupHomepage($search_term = '')
    {
        $gallup = RcpContestPollster::where('pi_contest_id', '=', 8)->where('name', '=', 'Gallup')->first();
        if ($this->pollsterNeedsScraping($gallup)) {
            $scrapes_per_minute = 2;
            for ($i=0; $i < $scrapes_per_minute; $i++) { 
                $delay = (int) ((60 / $scrapes_per_minute) * $i);
                $job = (new ScrapeGallupCongressHomePage($gallup))->delay($delay);
                $this->dispatch($job);
            }
        }
    }

    public function getCbs()
    {
        $cbs = RcpContestPollster::where('pi_contest_id', '=', 1)->where('name', '=', 'CBS')->first();
        if ($cbs->keep_scraping) {
            $scrapes_per_minute = 1;
            for ($i=0; $i < $scrapes_per_minute; $i++) { 
                $delay = (int) ((60 / $scrapes_per_minute) * $i);
                $job = (new ScrapeCbs($cbs))->delay($delay);
                $this->dispatch($job);
            }
        }
    }

    public function getIbd()
    {
        $ibd = RcpContestPollster::where('pi_contest_id', '=', 12)->where('name', '=', 'IBD/TIPP')->first();
        if ($ibd->keep_scraping) {
            $scrapes_per_minute = 1;
            for ($i=0; $i < $scrapes_per_minute; $i++) { 
                $delay = (int) ((60 / $scrapes_per_minute) * $i);
                $job = (new ScrapeIbd($ibd))->delay($delay);
                $this->dispatch($job);
            }
        }
    }

    public function getFoxPolls()
    {
        $fox = RcpContestPollster::where('pi_contest_id', '=', 1)->where('name', '=', 'FOX')->first();
        if ($fox->keep_scraping) {
            $scrapes_per_minute = 2;
            for ($i=0; $i < $scrapes_per_minute; $i++) { 
                $delay = (int) ((60 / $scrapes_per_minute) * $i);
                $job = (new ScrapeFoxPolls($fox))->delay($delay);
                $this->dispatch($job);
            }
        }
    }

    public function getQuin()
    {
        $quin = RcpContestPollster::where('pi_contest_id', '=', 1)->where('name', '=', 'Quin')->first();
        if ($quin->keep_scraping) {
            // $bot = new ScraperBot();
            // $bot->scrapeQuin($quin);
            $scrapes_per_minute = 1;
            for ($i=0; $i < $scrapes_per_minute; $i++) { 
                $delay = (int) ((60 / $scrapes_per_minute) * $i);
                $job = (new ScrapeQuin($quin))->delay($delay);
                $this->dispatch($job);
            }
        }
    }

    public function getEconomistWeekly()
    {
        $econ = RcpContestPollster::where('pi_contest_id', '=', 1)->where('name', '=', 'Economist/YouGov')->first();
        if ($this->pollsterNeedsScraping($econ)) {
            $scrapes_per_minute = 3;
            for ($i=0; $i < $scrapes_per_minute; $i++) { 
                $delay = (int) ((60 / $scrapes_per_minute) * $i);
                $job = (new ScrapeEconomist($econ))->delay($delay);
                $this->dispatch($job);
            }
        }
    }

    public function clearReuters()
    {
        $reuters = RcpContestPollster::where('name', '=', 'Reuters')
            ->update(['new_poll_update_text' => null, 'is_likely_final_for_week' => '']);
        $econ = RcpContestPollster::where('name', '=', 'Economist')
            ->update(['new_poll_update_text' => null]);
    }

    /**
     * Loop through weekly contests.
     * Get their pollsters and clear flag fields for the start of a new week.
     */
    public function clearWeekly()
    {
        $weeklies = array(1, 3, 8,);
        foreach ($weeklies as $contest_id) {
            $contest = PiContest::find($contest_id);
            $pollsters = $contest->last_rcp_update()->rcp_contest_pollsters_for_projections();
            
            foreach ($pollsters as $pollster) {
                $pollster->un_included_actual_result = '';
                $pollster->projected_result = '';
                $pollster->new_poll_update_text = '';
                $pollster->is_likely_final_for_week = '';
                $pollster->is_likely_dropout = '';
                $pollster->is_likely_addition = '';
                $pollster->save();
            }
        }
    }

    public function getReutersWeeklyReport()
    {
        $reuters = RcpContestPollster::where('pi_contest_id', '=', 1)->where('name', '=', 'Reuters')->first();
        if ($this->pollsterNeedsScraping($reuters)) {
            $scrapes_per_minute = 1;
            for ($i=0; $i < $scrapes_per_minute; $i++) { 
                $delay = (int) ((60 / $scrapes_per_minute) * $i);
                $job = (new ScrapeReutersWeekly($reuters))->delay($delay);
                $this->dispatch($job);
            }
        }
    }

    public function getReutersWeeklyUpdateObama()
    {
        // $reuters = RcpContestPollster::where('name', '=', 'Reuters')->where('pi_contest_id', '=', 1)->first();
        // if ($this->pollsterNeedsScraping($reuters)) {
        //     $bot = new ScraperBot();
        //     $bot->scrapeReutersWeeklyUpdate($reuters, 3, 'Approve', 'true');
        //     $this->handleResult($reuters, 'Obama Update', true);
        // }
    }

    public function getReutersWeeklyUpdateRighttrack()
    {
        $reuters = RcpContestPollster::where('name', '=', 'Reuters')->where('pi_contest_id', '=', 3)->first();
        if ($this->pollsterNeedsScraping($reuters)) {
            $bot = new ScraperBot();
            $bot->scrapeReutersWeeklyUpdate($reuters, 1, 'Right direction', 'false');
            $this->handleResult($reuters, 'RightTrack Update', true);
        }
    }

    public function getMonmouth()
    {
        $monmouth = RcpContestPollster::where('pi_contest_id', '=', 1)->where('name', '=', 'Monmouth')->first();
        if ($monmouth->keep_scraping) {
            $scrapes_per_minute = 1;
            for ($i=0; $i < $scrapes_per_minute; $i++) { 
                $delay = (int) ((60 / $scrapes_per_minute) * $i);
                $job = (new ScrapeMonmouth($monmouth))->delay($delay);
                $this->dispatch($job);
            }
        }
    }

    public function getPew()
    {
        $pew = RcpContestPollster::where('pi_contest_id', '=', 1)->where('name', '=', 'Pew')->first();
        if ($pew->keep_scraping) {
            $scrapes_per_minute = 1;
            for ($i=0; $i < $scrapes_per_minute; $i++) { 
                $delay = (int) ((60 / $scrapes_per_minute) * $i);
                $job = (new ScrapePew($pew))->delay($delay);
                $this->dispatch($job);
            }
        }
    }

    public function getPewForum()
    {
        $pew = RcpContestPollster::where('pi_contest_id', '=', 12)->where('name', '=', 'Pew')->first();
        if ($pew->keep_scraping) {
            $scrapes_per_minute = 1;
            for ($i=0; $i < $scrapes_per_minute; $i++) { 
                $delay = (int) ((60 / $scrapes_per_minute) * $i);
                $job = (new ScrapePewForum($pew))->delay($delay);
                $this->dispatch($job);
            }
        }
    }

    public function getAp()
    {
        $ap = RcpContestPollster::where('pi_contest_id', '=', 1)->where('name', '=', 'Associated Press')->first();
        if ($ap->keep_scraping) {
            $scrapes_per_minute = 1;
            for ($i=0; $i < $scrapes_per_minute; $i++) { 
                $delay = (int) ((60 / $scrapes_per_minute) * $i);
                $job = (new ScrapeAp($ap))->delay($delay);
                $this->dispatch($job);
            }
        }
    }

    public function getMarist()
    {
        $marist = RcpContestPollster::where('pi_contest_id', '=', 1)->where('name', '=', 'McClatchy/Marist')->first();
        if ($marist->keep_scraping) {
            $scrapes_per_minute = 1;
            for ($i=0; $i < $scrapes_per_minute; $i++) { 
                $delay = (int) ((60 / $scrapes_per_minute) * $i);
                $job = (new ScrapeMarist($marist))->delay($delay);
                $this->dispatch($job);
            }
        }
    }

    public function getBloomberg()
    {
        $bloomberg = RcpContestPollster::where('pi_contest_id', '=', 1)->where('name', '=', 'Bloomberg')->first();
        if ($bloomberg->keep_scraping) {
            $scrapes_per_minute = 1;
            for ($i=0; $i < $scrapes_per_minute; $i++) { 
                $delay = (int) ((60 / $scrapes_per_minute) * $i);
                $job = (new ScrapeBloomberg($bloomberg))->delay($delay);
                $this->dispatch($job);
            }
        }
    }

    public function getMtp()
    {
        $scrapes_per_minute = 1;
        for ($i=0; $i < $scrapes_per_minute; $i++) { 
            $delay = (int) ((60 / $scrapes_per_minute) * $i);
            $job = (new ScrapeMtp())->delay($delay);
            $this->dispatch($job);
        }
    }

    public function getSuffolk()
    {
        $suffolk = RcpContestPollster::where('pi_contest_id', '=', 1)->where('name', '=', 'USA Today/Suffolk')->first();
        if ($suffolk->keep_scraping) {
            $scrapes_per_minute = 1;
            for ($i=0; $i < $scrapes_per_minute; $i++) { 
                $delay = (int) ((60 / $scrapes_per_minute) * $i);
                $job = (new ScrapeSuffolk($suffolk))->delay($delay);
                $this->dispatch($job);

                // Job 2 just scrapes USAT politics page for keywords, but gets dispatched now.
                // $job_2 = (new ScrapeSuffolkUsat($suffolk))->delay($delay);
                // $this->dispatch($job_2);
            }
        }
    }

    public function getNationalJournal()
    {
        $nj = RcpContestPollster::where('pi_contest_id', '=', 1)->where('name', '=', 'National Journal')->first();
        if ($this->pollsterNeedsScraping($nj)) {
            $scrapes_per_minute = 2;
            for ($i=0; $i < $scrapes_per_minute; $i++) { 
                $delay = (int) ((60 / $scrapes_per_minute) * $i);
                $job = (new ScrapeNationalJournal($nj))->delay($delay);
                $this->dispatch($job);
            }
        }
    }

    public function getHuffPo()
    {
        $scrapes_per_minute = 1;
        for ($i=0; $i < $scrapes_per_minute; $i++) { 
            $delay = (int) ((60 / $scrapes_per_minute) * $i);
            $job = (new ScrapeHuffPo())->delay($delay);
            $this->dispatch($job);
        }

        // $bot = new ScraperBot();
        // $bot->scrapeHuffPo();
    }
}
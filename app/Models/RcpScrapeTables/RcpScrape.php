<?php

use Sunra\PhpSimple\HtmlDomParser;
use GuzzleHttp\Client;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\SendTextEmail;

use PredictIt\Trader;
use PredictIt\Navigator;

class RcpScrape extends Eloquent {
    use DispatchesJobs;

    private function hasChangesInTable($old_table, $new_table)
    {
        return $old_table != $new_table;
    }

    private function extractPollingTable1($page)
    {
        // We want the first iteration of this class of table.
        $table = substr($page, stripos($page, '<table class="data large ">'));
        $table = substr($table, 0, stripos($table, '</table>')+8);
        return $table;
    }

    private function extractPollingTable2($page)
    {
        // We want the second iteration of this class of table.
        $table = substr($page, stripos($page, '<table class="data large ">')+1);
        $table = substr($table, stripos($table, '<table class="data large ">'));
        $table = substr($table, 0, stripos($table, '</table>')+8);
        return $table;
    }

    private function clientGet($url)
    {
        $client = new Client;
        $response = $client->get($url);
        return $response->getBody()->getContents();
    }

    /**
     * Use these constants to avoid multiple records for small variances in Pollster names used.
     */
    public $pollster_name_constants = array(
        'NBC',
        'FOX',
        'CBS',
        'ABC',
        'CNN',
        'Gallup',
        'Rasmussen',
        'Reuters',
        'Quin',
        'PPP',
        'Associated Press',
        'The Economist',
        'Monmouth',
        'Pew',
        'USA Today/Suffolk',
        'National Journal',
        'Bloomberg',
    );

    public $scrape_year_start = 2016;
    public $scrape_month_start = 1;
    public $scrape_year_end = 2016;
    public $scrape_month_end = 1;

    public $percent_approval = '';
    public $percent_disapproval = '';
    public $scrape_polls = array();

    public function pi_contest()
    {
        return $this->belongsTo(PiContest::class);
    }

    public function scrape(PiContest $pi_contest)
    {
        // echo $pi_contest->last_rcp_update()->count_pollsters . "\n";
        $page = $this->clientGet($pi_contest->url_of_answer);
        if (!$page) {
            Log::info('URL did not load: ' . $pi_contest->url_of_answer);
            return;
        }

        $table1 = $this->extractPollingTable1($page);
        $table2 = $this->extractPollingTable2($page);
        
        // make sure you don't drop/re-add all polls just because a table failed to load
        if (!$table1 || !$table2) {
            Log::info('table did not load: ' . $pi_contest->url_of_answer);
            return;
        }

        if (!$this->hasChangesInTable($pi_contest->last_rcp_scrape_table_1, $table1) &&
            !$this->hasChangesInTable($pi_contest->last_rcp_scrape_table_2_long, $table2)
            ) {
            // Log::info($pi_contest->id . ': no changes');
            // echo "NO changes!!\n\n";
            return;
        }

        $scraper = new Scraper($pi_contest->url_of_answer);
        if ($pi_contest->id == 3) {
            $allHtml = $scraper->html;
            $loc_begin_parse = strpos($allHtml, 'Reason-Rupe');
            $loc_end_parse = strpos($allHtml, 'Zogby');
            $beginning = substr($allHtml, 0, $loc_begin_parse);
            $end = substr($allHtml, $loc_end_parse);
            $scraper->html = $beginning . $end;
        }
        $dom = HtmlDomParser::str_get_html($scraper->html);

        // Only save this RcpScrape record and do the data analysis if the URL and the table were found successfully.
        if ($dom) {
            $rows = $dom->find('div[id=polling-data-full] table tr');
            if ($rows) {
                $this->pi_contest_id = $pi_contest->id;
                $this->created_date = date('Y-m-d', strtotime('today'));
                $this->save();
                foreach ($dom->find('div[id=polling-data-full] table tr') as $tableRow) {
                    $this->extractPollingRow($tableRow);
                }
                $this->compareToLastUpdate();
            }

            $dom->clear();
            unset($dom);
        }

        // Save changes to the $pi_contest table fields.
        $pi_contest->last_rcp_scrape_table_1 = $table1;
        $pi_contest->last_rcp_scrape_table_2_long = $table2;
        $pi_contest->save();

        $drops_happening = false;
        $obama_drops_happening = false;
        $congress_drops_happening = false;
        $doc_drops_happening = false;
        if ($pi_contest->id == 1 && $pi_contest->last_rcp_update()->count_pollsters < 12) {
            $drops_happening = true;
            $obama_drops_happening = true;
        } elseif ($pi_contest->id == 3) {
            $drops_happening = true;
            if ($this->average >= 29) {
                $doc_drops_happening = true;
            }
        } elseif ($pi_contest->id == 8 && $pi_contest->last_rcp_update()->count_pollsters == 6) {
            $drops_happening = true;
            // if ($this->average >= 13) {
                $congress_drops_happening = true;
            // }
        }
        
        $o = new OneRing();
        if ($doc_drops_happening) {
            // $o->releaseNazgulTrade(26);
            // Log::info('DoC Economist update happening, unleashing Nazgul devastation');
        } elseif ($congress_drops_happening) {
            // $o->releaseNazgulTrade(26);
            // Log::info('Congress Economist update happening, unleashing Nazgul devastation');
        } elseif ($obama_drops_happening) {
            if ($this->average <= 50.1 && $this->average >= 49.9) {
                // $o->releaseNazgulTrade(24);
            }
            // Log::info('Obama Economist update happening, unleashing Nazgul devastation, if applicable');
        } elseif ($drops_happening) {
            // $o->removeNazgulObstacles(4);
            Log::info('drops happening, NOT cancelling orders');
        }

        if ($pi_contest->rcp_update_txt_alert) {
            $this->sendAlerts();
        }
    }

    /**
     * called as such:
     * makeTrade(1078, 'buy', 'Yes', 200, 57)
     */
    private function makeQuestionTrade($question_id, $buy_or_sell, $yes_or_no, $max_risk, $max_price)
    {
        $n = new Navigator();
        $t = new Trader();
        $trade_question = PiQuestion::find($question_id);
        $n->visitQuestionMarket($trade_question);
        $t->placeTrade($buy_or_sell, $yes_or_no, $max_risk, $max_price, $trade_question->url_of_market, $n->driver);
        usleep(5000);
    }

    private function sendAlerts()
    {
        $from = 'no@mm.dev';
        $subject = $this->average;
        $body = $this->pi_contest->name . ': ' . $this->update_text;
        echo $body;
        $job = (new SendTextEmail($from, $subject, $body))->onQueue('texts');
        $this->dispatch($job);

        $bot = new ScraperBot();
        $bot->keepUpdateBotWarm($this->pi_contest->url_of_answer);
        Cache::put('UpdateLocation', $this->pi_contest->url_of_answer, 15);
    }

    private function compareToLastUpdate()
    {
        $last_rcp_update = RcpUpdate::where('pi_contest_id', '=', $this->pi_contest_id)
            ->orderBy('id', 'desc')
            ->first()
        ;
        if ( ! $last_rcp_update) {
            $last_rcp_update = new RcpUpdate();
        }
        $last_update_polls = $last_rcp_update->rcp_update_pollsters->lists('rcp_contest_poll_id')->toArray();
        $this_scrape_polls = array();
        foreach ($this->scrape_polls as $poll) {
            $this_scrape_polls[] = $poll->id;
        }

        sort($last_update_polls);
        sort($this_scrape_polls);

        if ($last_update_polls == $this_scrape_polls) {
            return;
        }

        // Once we reach this point, we know there is an update. Check for an RcpDay and then make the RcpUpdate.
        $date = date('Y-m-d', strtotime('today'));
        $rcp_day = RcpDay::where('rcp_date', '=', $date)->first();
        if ( ! $rcp_day) {
            $rcp_day = new RcpDay();
            $rcp_day->rcp_date = $date;
            $rcp_day->save();
        }

        $rcp_update = new RcpUpdate();
        $rcp_update->rcp_time = date('H:i',strtotime('now'));
        $rcp_update->rcp_timestamp = $this->created_at;
        $rcp_update->pi_contest_id = $this->pi_contest_id;
        $rcp_update->rcp_day_id = $rcp_day->id;
        $rcp_update->percent_approval = $this->percent_approval;
        $rcp_update->percent_disapproval = $this->percent_disapproval;
        $rcp_update->save();

        foreach ($this->scrape_polls as $new) {
            $update_pollster = new RcpUpdatePollster();
            $update_pollster->rcp_update_id = $rcp_update->id;
            $update_pollster->rcp_contest_pollster_id = $new->rcp_contest_pollster_id;
            $update_pollster->rcp_contest_poll_id = $new->id;
            $update_pollster->save();
        }

        $this->has_change_since_last_scrape = 1;
        $this->update_number_today = $rcp_day->contestUpdates($this->pi_contest_id)->count();

        $new_polls = array_diff($this_scrape_polls, $last_update_polls);
        $dropped_polls = array_diff($last_update_polls, $this_scrape_polls);

        if ($new_polls) {
            $this->has_additions = 1;
            foreach ($new_polls as $poll) {
                $poll = RcpContestPoll::find($poll);
                $this->update_text .= 'Added ' . $poll->percent_favor . ' from ' . $poll->rcp_contest_pollster->name . "\n";
        
                $update_add = new RcpUpdateAdd();
                $update_add->rcp_update_id = $rcp_update->id;
                $update_add->rcp_contest_pollster_id = $poll->rcp_contest_pollster_id;
                $update_add->rcp_contest_poll_id = $poll->id;
                $update_add->save();
            }
        }
        
        if ($dropped_polls) {
            $this->has_dropouts = 1;
            foreach ($dropped_polls as $poll) {
                $poll = RcpContestPoll::find($poll);

                $update_drop = new RcpUpdateDrop();
                $update_drop->rcp_update_id = $rcp_update->id;
                $update_drop->rcp_contest_pollster_id = $poll->rcp_contest_pollster_id;
                $update_drop->rcp_contest_poll_id = $poll->id;
                $update_drop->save();

                $first = new DateTime($poll->date_end);
                $last = new DateTime();

                $poll->day_of_week_dropped_from_rcp = date('l', strtotime('today'));
                $poll->date_dropped_from_rcp_average = date('Y-m-d', strtotime('today'));
                $poll->age_of_poll_when_dropped_from_rcp = $first->diff($last)->format('%r%a');
                $poll->save();
        
                $add = new DateTime($poll->last_add->rcp_update->local_rcp_timestamp('Y-m-d H:i:s'));
                $drop = new DateTime($poll->last_drop->rcp_update->local_rcp_timestamp('Y-m-d H:i:s'));
                $length_in_average = $add->diff($drop);
                $poll->length_in_average = $length_in_average->format('%a days %h hours');
                $poll->save();

                $this->update_text .= 'Dropped ' . $poll->percent_favor . ' from ' . $poll->rcp_contest_pollster->name . "\n";
            }
        }
        $rcp_update->setDateRange();
        
        $this->save();
    }

    /**
     * THERE IS NOW AN ID# IN THE RCP ROW!
     */
    private function extractPollingRow($tableRow)
    {
        $rowType = $tableRow->class ? $tableRow->class : 'alt';
        $rowType = $rowType == 'alt isInRcpAvg' ? 'isInRcpAvg' : $rowType;

        $isNotHeader = $tableRow->find('td', 0);

        if ($isNotHeader) {
            if ($rowType == 'rcpAvg') {
                $this->average = $tableRow->find('td', 3)->plaintext;
                $this->save();
                $this->percent_approval = $tableRow->find('td', 3)->plaintext;
                $this->percent_disapproval = $tableRow->find('td', 4)->plaintext;
            }
            else {
                $pollster = $this->findRcpContestPollster($tableRow->find('td a', 0)->plaintext);
                $poll = $this->getPoll($tableRow, $pollster);
                if ($rowType == 'isInRcpAvg') {
                    $this->scrape_polls[] = $poll;
                    if ( ! $poll->date_added_to_rcp_average) {
                        // Mark the poll as being added to the average.
                        $poll->day_of_week_added_to_rcp = date('l', strtotime('today'));
                        $poll->date_added_to_rcp_average = date('Y-m-d', strtotime('today'));
                        $poll->save();
                        // Also wipe out the prior forecast values on the pollster.
                        // $pollster->un_included_actual_result = '';
                        // $pollster->projected_result = '';
                        // $pollster->new_poll_update_text = '';
                        // $pollster->save();
                    }
                }
            }
        }
    }

    /**
     * Do our best to find an existing big-name Pollster even if the name is slightly different.
     */
    private function findRcpContestPollster($pollster_name)
    {
        $pollster_name = str_replace('*', '', $pollster_name);
        $pollster_name = str_replace('-', '/', $pollster_name);
        foreach ($this->pollster_name_constants as $name) {
            if (stristr($pollster_name, $name)) {
                $pollster_name = $name;
                break;
            }
        }

        $pollster = RcpContestPollster::where('name', '=', $pollster_name)
            ->where('pi_contest_id', '=', $this->pi_contest_id)
            ->first()
        ;
        if ( ! $pollster) {
            $pollster = new RcpContestPollster();
            $pollster->pi_contest_id = $this->pi_contest_id;
            $pollster->name = $pollster_name;
            $pollster->save();
            $this->handleNewContestPollster($pollster);
        }
        return $pollster;
    }

    /**
     * If this is a new pollster added to an existing contest, something is up.
     */
    public function handleNewContestPollster(RcpContestPollster $pollster)
    {
        if ($this->pi_contest->rcp_updates->count() >1) {
            return;
        }

        $body = $this->pi_contest->name . ': ' . $pollster->name;
        echo $body;
        $job = (new SendTextEmail('no@mm.dev', 'new Pollster', $body))->onQueue('texts');
        $this->dispatch($job);
        return;
    }

    private function getPoll($tableRow, RcpContestPollster $pollster)
    {
        $date_range = $tableRow->find('td', 1)->plaintext;
        $date_start = substr($date_range, 0, strpos($date_range, '-') - 1);
        $date_end = substr($date_range, strpos($date_range, '-') + 2);

        $month_start = substr($date_start, 0, strpos($date_start, '/'));
        $month_end = substr($date_end, 0, strpos($date_end, '/'));

        if ($month_start == 1) {
            $this->scrape_month_start = 1;
        }
        elseif ($month_start == 12 && $this->scrape_month_start == 1) {
            $this->scrape_month_start = 12;
            $this->scrape_year_start--;
        }

        if ($month_end == 1) {
            $this->scrape_month_end = 1;
        }
        elseif ($month_end == 12 && $this->scrape_month_end == 1) {
            $this->scrape_month_end = 12;
            $this->scrape_year_end--;
        }

        if ($this->scrape_year_end < 2014) {
            return;
        }

        $date_start_fixed = date('Y-m-d', strtotime($date_start . '/' . $this->scrape_year_start));
        $date_end_fixed = date('Y-m-d', strtotime($date_end . '/' . $this->scrape_year_end));

        $sample = $tableRow->find('td', 2)->plaintext;
        $approval = $tableRow->find('td', 3)->plaintext;
        $disapproval = $tableRow->find('td', 4)->plaintext;
        $url_source_full_report = $tableRow->find('a', 0)->href;

        $poll = RcpContestPoll::where('rcp_contest_pollster_id', '=', $pollster->id)
            ->where('date_start', '=', $date_start_fixed)
            ->where('date_end', '=', $date_end_fixed)
            ->where('percent_favor', '=', $approval)
            ->where('percent_against', '=', $disapproval)
            ->first()
        ;
        if ( ! $poll) {
            $poll = new RcpContestPoll();
            $poll->rcp_contest_pollster_id = $pollster->id;
            $poll->date_start = $date_start_fixed;
            $poll->date_end = $date_end_fixed;
            $poll->sample = $sample;
            $poll->percent_favor = $approval;
            $poll->percent_against = $disapproval;
            $poll->url_source_full_report = $url_source_full_report;
            $poll->save();

            $poll->rcp_contest_pollster->clearCachedValues($poll);
            $poll->rcp_contest_pollster->clearPollsterBooleans();
        }
        return $poll;        
    }
}
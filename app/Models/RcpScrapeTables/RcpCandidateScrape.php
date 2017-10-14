<?php

use Sunra\PhpSimple\HtmlDomParser;
use GuzzleHttp\Client;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\SendTextEmail;
use PredictIt\Trader;
use PredictIt\Navigator;

class RcpCandidateScrape extends Eloquent {
    use DispatchesJobs;

    // no need to be TOO clever.
    // Table 1 is the highlighted table up top. Table 2 is the full history table below.
    public function scrapeRcp($contest_id = 187)
    {
        // Log::info($contest_id);
        $contest = PiContest::find($contest_id);
        $page = $this->clientGet($contest->url_of_answer);
        if (!$page) {
            Log::info('URL did not load: ' . $contest->url_of_answer);
            return;
        }

        $table1 = $this->extractPollingTable1($page);
        $table2 = $this->extractPollingTable2($page);
        
        // make sure you don't drop/re-add all polls just because a table failed to load
        if (!$table1 || !$table2) {
            Log::info('table did not load: ' . $contest->url_of_answer);
            return;
        }

        if (!$this->hasChangesInTable($contest->last_rcp_scrape_table_1, $table1) &&
            !$this->hasChangesInTable($contest->last_rcp_scrape_table_2_long, $table2)
            ) {
            // Log::info($contest_id . ': no changes');
            return;
        }

        $this->handleUpdate($contest, $table1, $table2);
    }

    /**
     * Once we reach this point, we know there is an update. 
     * Get the poll ids from the last update.
     * Make the RcpUpdate, then extract "meta-data" from table and set it on the Update record.
     * Then find the drops/adds and make their records.
     */
    private function handleUpdate(PiContest $contest, $table1, $table2)
    {
        $add_prri = false;

        $last_update_poll_ids = $this->getLastRcpUpdatePollIDs($contest);
        
        $rcp_update = $this->makeRcpUpdate($contest);
        $t2_dom = HtmlDomParser::str_get_html($table2);
        
        // Have to set the candidate averages on the Update.
        $candidate_columns = $this->extractCandidateNameColumns($t2_dom->find('th'));
        $candidate_averages = $this->extractCandidateAverages($t2_dom->find('tr[class=rcpAvg]', 0), $candidate_columns);
        $rcp_update->saveCandidateAverages($this->candidate_names, $candidate_averages);

        $this_scrape_polls = $this->getThisScrapePolls($t2_dom->find('tr'), $candidate_columns, $rcp_update);
        $this_scrape_poll_ids = collect($this_scrape_polls)->pluck('id')->toArray();
        $rcp_update->saveUpdatePollsters($this_scrape_polls);

        $new_polls = array_diff($this_scrape_poll_ids, $last_update_poll_ids);
        $dropped_polls = array_diff($last_update_poll_ids, $this_scrape_poll_ids);

        $update_text = $contest->name . "\n";
        if ($new_polls) {
            foreach ($new_polls as $poll_id) {
                $poll = RcpContestPoll::find($poll_id);
                $update_text .= 'Added ' . $poll->rcp_contest_pollster->name . ' ' . $poll->spread . "\n";
                $this->handleUpdateAddPoll($poll_id, $rcp_update);
                // if (stristr($poll->rcp_contest_pollster->name, 'PRRI')) {
                //     $add_prri = true;
                // }
            }
        }
        
        if ($dropped_polls) {
            foreach ($dropped_polls as $poll_id) {
                $poll = RcpContestPoll::find($poll_id);
                $update_text .= 'Dropped ' . $poll->rcp_contest_pollster->name . ' ' . $poll->spread . ', ' . $poll->current_poll_age . ' days old' . "\n";
                $this->handleUpdateDropPoll($poll_id, $rcp_update);
                // if ($poll->id == 21631) {
                // }
            }
        }
        $rcp_update->setDateRange();

        // Should I make a record for the re-arrangement if there is one?
        if (!$new_polls && !$dropped_polls && $this_scrape_poll_ids != $last_update_poll_ids) {
            $rearranged = true;
            $update_text .= 'Re-ordered rows' . "\n";
        } else {
            $rearranged = false;
        }

        // if ($this->hasChangesInTable($contest->last_rcp_scrape_table_1, $table1)) {
            // $this->handleTable1Changes($contest, $table1, $new_polls, $dropped_polls, $rearranged);
        // } 

        // Save changes to the $contest table fields.
        $contest->last_rcp_scrape_table_1 = $table1;
        $contest->last_rcp_scrape_table_2_long = $table2;
        $contest->save();
        
        $new_spread = $candidate_averages['Clinton'] - $candidate_averages['Trump'];
        Log::info($contest->name . ': ' . $new_spread);
        $o = new OneRing();

        if ($contest->id == 187) {
            if ($new_spread >= 7 && $new_spread < 70) {
                // $o->releaseNazgulTrade(34);
                // $o->releaseNazgulTrade(33);
            } elseif ($new_spread < 3) {
                // $o->releaseNazgulTrade(18);
                // echo "\nSELL NOW!!!!!!\n";
                // Log::info('SELL NOW!!');
            }
        } elseif ($contest->id == 225) {
            if ($new_spread >= 1) {
                // $o->releaseNazgulTrade(19);
            } elseif ($new_spread < 1) {
            }
        }

        $update_text .= $candidate_averages['spread'];
        $this->sendAlerts($contest, $update_text);
    }

    /**
     * Changes to Table 1. Not sure this ever happens independently. By definition, should also change Table 2.
     * Handling here is primarily for action - identify what the change is and how to react.
     * Different # of columns?
     * Different # of rows?
     * Different data in the same rows?
     *   - Updating pollster(s)
     *   - Replacing pollster(s) with different pollster(s)
     *   - Both of the above.
     * Re-ordering?
     */
    private function handleTable1Changes(PiContest $contest, $table1, $new_polls, $dropped_polls, $rearranged)
    {
        // $t1_dom = HtmlDomParser::str_get_html($table1);
        if ($new_polls || $dropped_polls) {
            d('there is new data. go do things with it!');
            // $this->handleRcpAverageUpdate();
            // Note add/drop/update/replacement in text messages.
        }
        // The same polls are in the table, but they were re-ordered. Relatively rare and unclear implications.
        elseif ($rearranged) {
            d('different order of rows. not sure what to do next.');
            // $this->handleRearrange();
        }

        // Different number of columns is independent of any other changes. Same polls, different candidates.
        if ($this->tablesHaveDifferentNumberOfColumns($contest->last_rcp_scrape_table_1, $table1)) {
            d('different # of columns');
        }
    }

    private function sendAlerts(PiContest $contest, $update_text='')
    {
        echo $update_text;
        $job = (new SendTextEmail($this->txt_from, $update_text, $update_text))->onQueue('texts');
        $this->dispatch($job);

        $bot = new ScraperBot();
        $bot->keepUpdateBotWarm($contest->url_of_answer);
    }

    private function tablesHaveDifferentNumberOfColumns($old_table, $new_table)
    {
        return substr_count($old_table, '<th') != substr_count($new_table, '<th');
    }

    /**
     * If we have made it this far, one of two situations must have occurred:
     *  - Number of columns WERE changed. Polls were NOT changed. Poll order MAY OR MAY NOT have changed.
     *  - Number of columns NOT changed.  Polls were NOT changed. Poll order must have changed.
     */
    private function handleRearrange($old_poll_ids, $new_poll_ids, $rearranged)
    {
        // To get this far, they SHOULD BE identical after being sorted
        $old_poll_ids_sorted = sort($old_poll_ids);
        $new_poll_ids_sorted = sort($new_poll_ids);
        if ($old_poll_ids_sorted != $new_poll_ids_sorted) {
            d('this is weird, you should not be here if the polls are not identical');
            die();
        }
        // But did RCP change the pre-sorted order?
        elseif ($old_poll_ids != $new_poll_ids) {
            d('RCP updated sort order, found what we are looking for!');
            die();
            return true;
        }
        else {
            d('We must be here because of a change in columns');
            die();
            return false;
        }
    }

    private function hasChangesInTable($old_table, $new_table)
    {
        return $old_table != $new_table;
    }

    /**
     * Get the Candidate name column #'s out of the row headers and return them as an array.
     */
    private function extractCandidateNameColumns($headers)
    {
        $columns = [];
        $i = 0;
        foreach ($headers as $header) {
            foreach ($this->candidate_names as $candidate) {
                if (stristr($header->plaintext, $candidate)) {
                    $columns[$candidate] = $i;
                }
            }
            $i++;
        }
        return $columns;
    }

    /**
     * Get each candidate's specified column out of the RCP Average row.
     * If a candidate's column is missing, we don't care at this stage.
     */
    private function extractCandidateAverages($tableRow, $candidate_columns)
    {
        $averages = [];
        $averages['date_range'] = $tableRow->find('td', 1)->plaintext;
        foreach ($this->candidate_names as $candidate) {
            if (isset($candidate_columns[$candidate])) {
                $averages[$candidate] = $tableRow->find('td', $candidate_columns[$candidate])->plaintext;
            }
        }
        return $averages;
    }

    /**
     * Get the most recent RcpUpdate for a PiContest.
     * Return an array of the rcp_contest_poll_id that were on that RcpUpdate.
     */
    private function getLastRcpUpdatePollIDs(PiContest $contest)
    {
        $last_rcp_update = RcpUpdate::where('pi_contest_id', '=', $contest->id)
            ->orderBy('rcp_timestamp', 'desc')
            ->first()
        ;
        if ( ! $last_rcp_update) {
            $last_rcp_update = new RcpUpdate();
        }
        return $last_rcp_update->rcp_update_pollsters->lists('rcp_contest_poll_id')->toArray();
    }

    /**
     * Get the RcpContestPolls of the polls in this scrape.
     * Create new RcpContestPoll and Pollster records when needed.
     */
    private function getThisScrapePolls($tableRows, $candidate_columns, RcpUpdate $rcp_update)
    {
        $this_scrape_polls = [];
        foreach ($tableRows as $tableRow) {
            if ($tableRow->class != 'header' && $tableRow->class != 'rcpAvg') {
                $pollster_name = $tableRow->find('td a', 0)->plaintext;
                $pollster_id = $rcp_update->findRcpContestPollster($pollster_name);
                $poll = $this->getPoll($tableRow, $pollster_id, $candidate_columns);
                
                if ($tableRow->class == 'isInRcpAvg' || $tableRow->class == 'alt isInRcpAvg') {
                    $this_scrape_polls[] = $poll;
                    if ( ! $poll->date_added_to_rcp_average) {
                        // Mark the poll as being added to the average.
                        $poll->day_of_week_added_to_rcp = date('l', strtotime('today'));
                        $poll->date_added_to_rcp_average = date('Y-m-d', strtotime('today'));
                        $poll->save();
                    }
                }
            }
        }
        return $this_scrape_polls;
    }

    /**
     * We have an update. Save $this record to create the timestamps.
     * Create RcpUpdate and set some fields.
     * Return the RcpUpdate.
     */
    private function makeRcpUpdate(PiContest $contest)
    {
        $this->save();
        $rcp_update = new RcpUpdate();
        $rcp_update->saveNewFromScrape($this, $contest);
        return $rcp_update;
    }

    private function handleUpdateAddPoll($poll_id, $rcp_update)
    {
        $poll = RcpContestPoll::find($poll_id);
        $update_add = new RcpUpdateAdd();
        $update_add->saveNewFromScrape($rcp_update->id, $poll->rcp_contest_pollster_id, $poll->id);
    }

    private function handleUpdateDropPoll($poll_id, $rcp_update)
    {
        $poll = RcpContestPoll::find($poll_id);
        $update_drop = new RcpUpdateDrop();
        $update_drop->saveNewFromScrape($rcp_update->id, $poll->rcp_contest_pollster_id, $poll->id);

        $poll->saveDropData();
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
     * Use data from this row to see if we have a matching Poll record.
     * If there is ever a corrected date range or poll value (spread), 
     * it will register as a new Poll.
     */
    private function getPoll($tableRow, $pollster_id, $columns)
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

        $spread = $tableRow->find('td', $columns['spread'])->plaintext;
        $johnson = isset($columns['Johnson']) ? $tableRow->find('td', $columns['Johnson'])->plaintext : NULL;
        $clinton = isset($columns['Clinton']) ? $tableRow->find('td', $columns['Clinton'])->plaintext : NULL;
        $trump = isset($columns['Trump']) ? $tableRow->find('td', $columns['Trump'])->plaintext : NULL;
        $stein = isset($columns['Stein']) ? $tableRow->find('td', $columns['Stein'])->plaintext : NULL;

        $poll = RcpContestPoll::where('rcp_contest_pollster_id', '=', $pollster_id)
            ->where('date_start', '=', $date_start_fixed)
            ->where('date_end', '=', $date_end_fixed)
            ->where('spread', '=', $spread)
            ->where('Johnson', '=', $johnson)
            ->where('Clinton', '=', $clinton)
            ->where('Trump', '=', $trump)
            ->where('Stein', '=', $stein)
            ->first()
        ;
        if ( ! $poll) {
            $poll = new RcpContestPoll();
            $poll->rcp_contest_pollster_id = $pollster_id;
            $poll->date_start = $date_start_fixed;
            $poll->date_end = $date_end_fixed;
            $poll->sample = $tableRow->find('td', 2)->plaintext;
            $poll->url_source_full_report = $tableRow->find('a', 0)->href;
            // Save all candidate scores here
            foreach ($this->candidate_names as $candidate) {
                if (isset($columns[$candidate])) {
                    $poll->$candidate = $tableRow->find('td', $columns[$candidate])->plaintext;
                }
            }
            $poll->save();
            $poll->rcp_contest_pollster->clearCachedValues($poll);
            $poll->rcp_contest_pollster->clearPollsterBooleans();
        }
        return $poll;        
    }

    public $candidate_names = [
        'Clinton',
        'Trump',
        'Johnson',
        'Stein',
        'spread',
    ];

    /**
     * Date constants for rollover to New Year.
     */
    public $scrape_year_start = 2016;
    public $scrape_month_start = 1;
    public $scrape_year_end = 2016;
    public $scrape_month_end = 1;

    /**
     * Misc constants.
     */
    public $pi_contest_id = '';
    public $scrape_polls = array();
    public $txt_from = 'no@mm.dev';

    /**
     * Relationships to the old-school DEM/GOP Primary data.
     */
    private function getLastDemUpdate()
    {
        return RcpCandidateDemUpdate::orderBy('id', 'desc')->first();
    }

    private function getLastGopUpdate()
    {
        return RcpCandidateGopUpdate::orderBy('id', 'desc')->first();
    }
}
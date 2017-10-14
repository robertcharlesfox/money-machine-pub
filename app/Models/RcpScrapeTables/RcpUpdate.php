<?php

use Sunra\PhpSimple\HtmlDomParser;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\SendTextEmail;

define('ID_OBAMA_GALLUP', 1345);
define('ID_OBAMA_RASMUSSEN', 1349);
define('ID_OBAMA_REUTERS', 1346);
define('ID_OBAMA_ECONOMIST', 1351);
define('ID_OBAMA_CONTEST', 1);

/**
 * This model represents an Update to the collection of RcpContestPolls for a PiContest.
 * These are valuable to record to see patterns in when updates happen.
 * Aim is to predict future RCP Averages by knowing exactly which Polls will be included and which won't.
 * These are created in 2 ways:
 *  1) MoneyMachine cron scrapes of RCP that show new updates.
 *  2) MoneyMachine import scrapes of Michanikos data that contain historical updates and timestamps.
 */
class RcpUpdate extends Eloquent {
    use DispatchesJobs;

    /**
     * Use these constants to avoid multiple records for small variances in Pollster names used.
     */
    public $pollster_name_constants = array(
        'LA Times',
        'GWU/Battleground',
        'Gravis',
        'NBC News/SM',
        'MSNBC',
        'CNBC',
        'NBC',
        'USA Today/Suffolk',
        'Suffolk',
        'Monmouth',
        'Elon',
        'Quin',
        'Opinion Savvy',
        'SurveyUSA',
        'FOX',
        'CBS',
        'ABC',
        'CNN',
        'Gallup',
        'Rasmussen',
        'Reuters',
        'PPP',
        'Associated Press',
        'The Economist',
        'Pew',
        'National Journal',
        'Bloomberg',
        'IBD',
    );

    private $pollsters_for_projections = '';
    private $debate_pollsters = '';
    private $recent_polls = '';

    public function previousUpdate()
    {
        $previous = RcpUpdate::where('id', '<', $this->id)
            ->where('pi_contest_id', '=', $this->pi_contest_id)
            ->orderBy('id', 'desc')
            ->first();
        return $previous ? $previous : new RcpUpdate;
    }

    public function pi_contest()
    {
        return $this->belongsTo(PiContest::class);
    }

    public function rcp_day()
    {
        return $this->belongsTo(RcpDay::class);
    }

    public function rcp_update_adds()
    {
        return $this->hasMany(RcpUpdateAdd::class);
    }

    public function rcp_update_drops()
    {
        return $this->hasMany(RcpUpdateDrop::class);
    }

    public function rcp_update_pollsters()
    {
        return $this->hasMany(RcpUpdatePollster::class);
    }

    public function rcp_drop_trades()
    {
        return $this->hasMany(RcpDropTrade::class);
    }

    public function rcp_add_trades()
    {
        return $this->hasMany(RcpAddTrade::class);
    }

    public function local_rcp_timestamp($date_format = 'Y-m-d g:i a l')
    {
        if ($this->pi_contest_id == 12 || $this->pi_contest_id == 13) {
            $micha_end_date = date('Y-m-d H:i:s', strtotime('10/12/2015 2:28 pm'));
        }
        else {
            $micha_end_date = date('Y-m-d H:i:s', strtotime('9/29/2015 4:36 pm'));
        }
        if ($this->created_at < $micha_end_date) {
            return date($date_format, strtotime($this->rcp_timestamp . ' -4hours'));
        }
        return date($date_format, strtotime($this->rcp_timestamp));
    }

    public function updateSummary()
    {
        $average = $this->percent_approval;
        $time = $this->local_rcp_timestamp('g:i a') . ', ';
        $adds = $this->count_adds . ' adds, ';
        $drops = $this->count_drops . ' drops, ';
        $pollsters = $this->count_pollsters . ' pollsters, ';
        $oldest = 'oldest is ' . $this->oldest_poll . ', second-oldest is ' . $this->second_oldest_poll . ', ';
        $length = 'date range is ' . $this->date_range_length;
        return $time . $adds . $drops . $pollsters . $oldest . $length;
    }

    /**
     * Return not only the related RcpUpdatePollsters, but also related RcpContestPollsters with an un-included result.
     * Cache the value on the object so we don't need to calculate it 20 times per page load.
     */
    public function rcp_contest_pollsters_for_projections()
    {
        if ( ! $this->pollsters_for_projections) {
            $u_p = $this->rcp_update_pollsters;
            $c_p = array();
            foreach ($u_p as $update_pollster) {
                $c_p[] = $update_pollster->rcp_contest_pollster;
            }

            $un_updated_pollsters = $this->pi_contest->rcp_contest_pollsters()->where('un_included_actual_result', '>', 0)->get();
            foreach ($un_updated_pollsters as $update_pollster) {
                if ( ! in_array($update_pollster, $c_p)) {
                    $c_p[] = $update_pollster;
                }
            }

            $likely_additions = $this->pi_contest->rcp_contest_pollsters()->where('is_likely_addition', '=', 1)->get();
            foreach ($likely_additions as $update_pollster) {
                if ( ! in_array($update_pollster, $c_p)) {
                    $c_p[] = $update_pollster;
                }
            }

            $this->pollsters_for_projections = $c_p;
            // $this->pollsters_for_projections = array_reverse($c_p);
        }
        return $this->pollsters_for_projections;
    }

    public function debate_pollsters()
    {
        if ( ! $this->debate_pollsters) {
            $this->debate_pollsters = $this->pi_contest->rcp_contest_pollsters()->where('debate_eligible_poll', '=', 1)->get();
        }
        return $this->debate_pollsters;
    }

    public function recent_polls($candidate, $pollster = '')
    {
        $polls_to_use = $candidate ? 7 : 10;
        if ($pollster && $pollster->pi_contest_id == ID_OBAMA_CONTEST) {
            if ( ! isset($this->recent_polls[$pollster->id])) {
                switch ($pollster->id) {
                    case ID_OBAMA_REUTERS:
                        $this->recent_polls[$pollster->id] = $pollster->sorted_polls()
                            ->take($polls_to_use);
                        break;
                    
                    case ID_OBAMA_ECONOMIST:
                        $this->recent_polls[$pollster->id] = $pollster->sorted_polls()
                            ->take($polls_to_use);
                        break;
                    
                    default:
                        $this->recent_polls[$pollster->id] = $this->pi_contest->rcp_contest_polls()
                            ->orderBy('date_end', 'desc')
                            ->where('rcp_contest_pollster_id', '<>', ID_OBAMA_GALLUP)
                            ->where('rcp_contest_pollster_id', '<>', ID_OBAMA_RASMUSSEN)
                            ->where('rcp_contest_pollster_id', '<>', ID_OBAMA_REUTERS)
                            ->where('rcp_contest_pollster_id', '<>', ID_OBAMA_ECONOMIST)
                            ->get()
                            ->take($polls_to_use);
                        break;
                }
            }
            return $this->recent_polls[$pollster->id];
        }
        elseif ($this->pi_contest_id == ID_OBAMA_CONTEST) {
            if ( ! isset($this->recent_polls['random'])) {
                $this->recent_polls['random'] = $this->pi_contest->rcp_contest_polls()
                    ->orderBy('date_end', 'desc')
                    ->where('rcp_contest_pollster_id', '<>', ID_OBAMA_GALLUP)
                    ->where('rcp_contest_pollster_id', '<>', ID_OBAMA_RASMUSSEN)
                    ->where('rcp_contest_pollster_id', '<>', ID_OBAMA_REUTERS)
                    ->where('rcp_contest_pollster_id', '<>', ID_OBAMA_ECONOMIST)
                    ->get()
                    ->take($polls_to_use);
            }
            return $this->recent_polls['random'];
        }
        elseif ( ! $this->recent_polls) {
            $this->recent_polls = $this->pi_contest->rcp_contest_polls()->orderBy('date_end', 'desc')->get()->take($polls_to_use);
        }
        return $this->recent_polls;
    }

    /**
     * Average of all pollster's latest result -- EXCEPT those marked as likely to drop out.
     */
    public function avgMinusLikelyDropouts($candidate = '')
    {
        $poll_values = array();

        foreach ($this->rcp_contest_pollsters_for_projections() as $pollster) {
            if ( ! $pollster->is_likely_dropout) {
                $poll_values[] = $pollster->avgInclusionValue($candidate);
            }
        }
        return $this->formatAverages($poll_values, $candidate);
    }

    /**
     * Average of RCP poll numbers that have been marked as "final" for the relevant time period.
     */
    public function avgWithFridaysFinals($candidate = '')
    {
        $poll_values = array();

        foreach ($this->rcp_contest_pollsters_for_projections() as $pollster) {
            if ($pollster->is_likely_final_for_week) {
                $poll_values[] = $pollster->avgInclusionValue($candidate);
            }
        }
        return $this->formatAverages($poll_values, $candidate);
    }

    private function formatAverages($poll_values, $candidate)
    {
        if (count($poll_values)) {
            if ($candidate) {
                return number_format((array_sum($poll_values) / count($poll_values)), 1);
            }
            return number_format((array_sum($poll_values) / count($poll_values)), 2) . ' - ' . count($poll_values) . ' polls';
        }
        return 'No Polls';
    }

    /**
     * Average of RCP Finals, Un-included results, and Projections.
     */
    public function avgWithFinalsAndProjections($include_estimates = false, $candidate = '', $threshold = '', $value_only = false, $include_randoms = false)
    {
        $poll_values = array();

        foreach ($this->rcp_contest_pollsters_for_projections() as $pollster) {
            if ($pollster->is_likely_final_for_week) {
                $poll_values[] = $pollster->avgInclusionValue($candidate);
            }
            elseif ($pollster->un_included_actual_result > 0) {
                $poll_values[] = $pollster->un_included_actual_result;
            }
            elseif ($pollster->projected_result > 0) {
                $poll_values[] = $pollster->projected_result;
            }
            elseif ($include_estimates && $pollster->id == ID_OBAMA_GALLUP) {
                $poll_values[] = $pollster->gallupProjAvg();
            }
            elseif ($include_estimates && $pollster->id == ID_OBAMA_RASMUSSEN) {
                $poll_values[] = $pollster->rasmussenProjAvg();
            }
            elseif ($include_estimates && ! $pollster->is_likely_dropout) {
                $poll_values[] = $pollster->trendForecast($this->recent_polls($candidate, $pollster), $candidate);
            }
        }

        if ($include_randoms) {
            for ($i=0; $i < $this->pi_contest->random_polls_to_add; $i++) { 
                $poll_values[] = $this->randomPoll($candidate, 0, true);
            }
        }
    
        if (count($poll_values)) {
            if ($candidate) {
                $avg = number_format((array_sum($poll_values) / count($poll_values)), 1);
                $avg = $threshold ? number_format($avg - $threshold, 1) : $avg;
                $stdev = number_format($this->totalStDev($candidate, $include_randoms), 1);
                $range = ' (' . ($avg - $stdev) . ' - ' . ($avg + $stdev) . ') ';
                return $value_only ? $avg : $avg . ' ± ' . $stdev . $range;
            }
            $avg = number_format((array_sum($poll_values) / count($poll_values)), 2);
            $stdev = $this->totalStDev($candidate, $include_randoms);
            $range = ' (' . ($avg - $stdev) . ' - ' . ($avg + $stdev) . ') ';
            $polls = ' - ' . count($poll_values) . ' polls';
            return $avg . ' ± ' . $stdev . $range . $polls;
        }
        return 'No Polls';
    }

    /**
     * Calculates the average/stdev of the last 10 polls for this contest, regardless of pollster.
     */
    public function randomPoll($candidate = '', $threshold = '', $value_only = false) {
        $polls = $this->recent_polls($candidate);
        if ($polls->count() > 0) {
            if ($candidate) {
                $poll_values = $polls->pluck($candidate)->all();
                $poll_values[] = $polls->first()->$candidate;

                $avg = number_format((array_sum($poll_values) / count($poll_values)), 1);
                $avg = $threshold ? number_format($avg - $threshold, 1) : $avg;
                $stdev = number_format($this->standard_deviation($poll_values), 1);
                $range = ' (' . ($avg - $stdev) . ' - ' . ($avg + $stdev) . ') ';
                return $value_only ? $avg : $avg . ' ± ' . $stdev . $range;
            }
            else {
                $poll_values = $polls->pluck('percent_favor')->all();
                $poll_values[] = $polls->first()->percent_favor;

                $avg = number_format((array_sum($poll_values) / count($poll_values)), 2);
                $stdev = number_format($this->standard_deviation($poll_values), 1);
                $range = ' (' . ($avg - $stdev) . ' - ' . ($avg + $stdev) . ') ';
                $polls = ' - ' . count($poll_values) . ' polls';
                return $value_only ? $avg : $avg . ' ± ' . $stdev . $range . $polls;
            }
        }
    }
    
    function standard_deviation($aValues, $bSample = false)
    {
        $fMean = array_sum($aValues) / count($aValues);
        $fVariance = 0.0;
        foreach ($aValues as $i)
        {
            $fVariance += pow($i - $fMean, 2);
        }
        $fVariance /= ( $bSample ? count($aValues) - 1 : count($aValues) );
        return (float) sqrt($fVariance);
    }

    /**
     * For non-final, non-dropout, non-projections, sum up the remaining polls' StDev.
     * Then divide by the total number of polls as a means of getting StDev for the entire average.
     * Not sure if this is mathematically the best way of doing this, but it's a start.
     */
    public function totalStDev($candidate = '', $include_randoms = false)
    {
        $poll_values = array();

        foreach ($this->rcp_contest_pollsters_for_projections() as $pollster) {
            if ($pollster->is_likely_final_for_week) {
                $poll_values[] = 0;
            }
            elseif ($pollster->id == ID_OBAMA_GALLUP) {
                $poll_values[] = $pollster->gallupStDev();
            }
            elseif ($pollster->id == ID_OBAMA_RASMUSSEN) {
                $poll_values[] = $pollster->rasmussenStDev();
            }
            elseif ($pollster->un_included_actual_result > 0) {
                $poll_values[] = 0;
            }
            elseif ($pollster->projected_result > 0) {
                $poll_values[] = ($pollster->trendStDev($this->recent_polls($candidate, $pollster), $candidate) / 2);
            }
            elseif ( ! $pollster->is_likely_dropout) {
                $poll_values[] = $pollster->trendStDev($this->recent_polls($candidate, $pollster), $candidate);
            }
        }

        if ($include_randoms) {
            $prior_polls = $this->recent_polls($candidate);
            if ($candidate) {
                $prior_poll_values = $prior_polls->pluck($candidate)->all();
                $prior_poll_values[] = $prior_polls->first()->$candidate;
            }
            else {
                $prior_poll_values = $prior_polls->pluck('percent_favor')->all();
                $prior_poll_values[] = $prior_polls->first()->percent_favor;
            }
            $stdev = number_format($this->standard_deviation($prior_poll_values), 1);
            for ($i=0; $i < $this->pi_contest->random_polls_to_add; $i++) { 
                $poll_values[] = $stdev;
            }
        }
        
        if (array_sum($poll_values)) {
            $rounding = $candidate ? 1 : 2;
            return number_format((array_sum($poll_values) / count($poll_values)), $rounding);
        }
        return 0.01;
    }

    public function tvc_valuation($x, $x2, $projections)
    {
        $ave = $projections['average'];
        $stdev = max($projections['variance'], 0.05);

        // Subtract a half point for rounding.
        $z = ($x - 0.05 - $ave) / $stdev;
        $y = 1 - $this->cumnormdist($z);
        
        $z2 = ($x2 - 0.05 - $ave) / $stdev;
        $y2 = 1 - $this->cumnormdist($z2);
        return sprintf("%.0f%%", ($y - $y2) * 100);
    }

    public function binary_valuation($x, $update_values)
    {
        $ave = $update_values['average'];
        $stdev = $update_values['variance'];

        // Subtract a half point for rounding.
        $z = ($x - 0.05 - $ave) / $stdev;
        $y = 1 - $this->cumnormdist($z);
        
        $z2 = ($x2 - 0.05 - $ave) / $stdev;
        $y2 = 1 - $this->cumnormdist($z2);
        return sprintf("%.0f%%", ($y - $y2) * 100);
    }

    public function valuation($x, $x2 = '', $candidate = '')
    {
        $ave = $this->avgWithFinalsAndProjections(true, $candidate, '', false, true);
        $stdev = $this->totalStDev($candidate, true);

        // Subtract a half point for rounding.
        $z = ($x - 0.05 - $ave) / $stdev;
        $y = 1 - $this->cumnormdist($z);
        
        if ($x2) {
            $z2 = ($x2 - 0.05 - $ave) / $stdev;
            $y2 = 1 - $this->cumnormdist($z2);
            return sprintf("%.0f%%", ($y - $y2) * 100);
        }
        return sprintf("%.0f%%", $y * 100);
    }

    private function cumnormdist($x)
    {
      $b1 =  0.319381530;
      $b2 = -0.356563782;
      $b3 =  1.781477937;
      $b4 = -1.821255978;
      $b5 =  1.330274429;
      $p  =  0.2316419;
      $c  =  0.39894228;

      if($x >= 0.0) {
          $t = 1.0 / ( 1.0 + $p * $x );
          return (1.0 - $c * exp( -$x * $x / 2.0 ) * $t *
          ( $t *( $t * ( $t * ( $t * $b5 + $b4 ) + $b3 ) + $b2 ) + $b1 ));
      }
      else {
          $t = 1.0 / ( 1.0 - $p * $x );
          return ( $c * exp( -$x * $x / 2.0 ) * $t *
          ( $t *( $t * ( $t * ( $t * $b5 + $b4 ) + $b3 ) + $b2 ) + $b1 ));
        }
    }    

    /**
     *
     * BEGIN RCP SCRAPE DATA INTEGRATION METHODS HERE
     *
     */

    public function saveNewFromScrape($scrape, PiContest $contest)
    {
        $date = date('Y-m-d', strtotime('today'));
        $rcp_day = RcpDay::where('rcp_date', '=', $date)->first();
        if ( ! $rcp_day) {
            $rcp_day = new RcpDay();
            $rcp_day->rcp_date = $date;
            $rcp_day->save();
        }

        $this->rcp_time = date('H:i',strtotime('now'));
        $this->rcp_timestamp = $scrape->created_at;
        $this->pi_contest_id = $contest->id;
        $this->rcp_day_id = $rcp_day->id;
        $this->save();
    }

    public function saveCandidateAverages($candidate_names, $averages)
    {
        foreach ($candidate_names as $candidate) {
            if (isset($averages[$candidate])) {
                $this->$candidate = $averages[$candidate];
            }
        }
        $this->save();
    }

    public function saveUpdatePollsters($scrape_polls)
    {
        foreach ($scrape_polls as $poll) {
            $update_pollster = new RcpUpdatePollster();
            $update_pollster->saveNewFromScrape($this->id, $poll->rcp_contest_pollster_id, $poll->id);
        }
    }

    /**
     * Calculate the polls' date range width, the oldest poll, count related poll/sters.
     * Cache these on the record to they are super-easy to access at any time.
     */
    public function setDateRange()
    {
        $rcp_update_pollsters = $this->rcp_update_pollsters;

        $sorted_asc = $rcp_update_pollsters->sortBy(function ($pollster, $key) {
            return $pollster->rcp_contest_poll->date_start;
        });
        $first = $sorted_asc->first()->rcp_contest_poll;
        $first = new DateTime(date('Y-m-d', strtotime($first->date_start)));

        // Find the second-oldest poll, which might also be useful information.
        $second_first = $sorted_asc->take(2)->last()->rcp_contest_poll;
        $second_first = new DateTime(date('Y-m-d', strtotime($second_first->date_end)));

        $sorted_desc = $rcp_update_pollsters->sortByDesc(function ($pollster, $key) {
            return $pollster->rcp_contest_poll->date_end;
        });
        $last = $sorted_desc->first()->rcp_contest_poll;
        $last = new DateTime(date('Y-m-d', strtotime($last->date_end)));
        
        $rcp_date = new DateTime($this->rcp_day->rcp_date);

        $this->date_range = $first->format('m/d') . ' - ' . $last->format('m/d');
        $this->date_range_length = $first->diff($last)->format('%r%a');
        $this->oldest_poll = $first->diff($rcp_date)->format('%r%a');
        $this->second_oldest_poll = $second_first->diff($rcp_date)->format('%r%a');
        $this->count_pollsters = $this->rcp_update_pollsters->count();
        $this->count_adds = $this->rcp_update_adds->count();
        $this->count_drops = $this->rcp_update_drops->count();
        $this->save();
    }

    /**
     * Do our best to find an existing big-name Pollster even if the name is slightly different.
     */
    public function findRcpContestPollster($pollster_name)
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
        return $pollster->id;
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

    /**
     * If there is an old update, begin by making copies of its pollsters for this update.
     * They will either be kept, deleted, or otherwise updated in later methods.
     */
    public function makeUpdatePollsters($last_update)
    {
        return;

        if ($last_update) {
            foreach ($last_update->rcp_update_pollsters as $old) {
                $update_pollster = new RcpUpdatePollster();
                // NEED TO UPDATE THIS IF EVER USE IT AGAIN
                // $update_pollster->saveNewFromScrape($this->id, $old->rcp_contest_pollster_id, $old->rcp_contest_poll_id);
            }
            $this->setDateRange();
        }
    }

    /**
     * If deleting or updating, need to figure out which poll this was, and update it.
     * Anything other than a 'delete' needs a new record saved.
     */
    public function processMichaPoll($poll)
    {
        switch ($poll->type) {
            case 'init': case 'add':
                $this->handleAddPoll($poll);
                break;
            
            case 'delete':
                $this->handleDeletePoll($poll);
                break;
            
            case 'update':
                $this->handleDeletePoll($poll);
                $this->handleAddPoll($poll);
                break;
        }
    }

    /**
     * FOR MICHANIKOS IMPORTS ONLY.
     * Calculate our date ranges.
     * Create/update a Poll as needed.
     * Create RcpUpdatePollster/Add related records.
     */
    private function handleAddPoll($micha_poll) {
        $weekday = date('l', strtotime($micha_poll->timestamp));
        $day_of_timestamp = date('Y-m-d', strtotime($micha_poll->timestamp));
        $date_start = substr($micha_poll->dates, 0, strpos($micha_poll->dates, '-') - 1);
        $date_end = substr($micha_poll->dates, strpos($micha_poll->dates, '-') + 2);
        $date_start_fixed = date('Y-m-d', strtotime($date_start . '/2015'));
        $date_end_fixed = date('Y-m-d', strtotime($date_end . '/2015'));
        $rcp_contest_pollster_id = $this->findRcpContestPollster($micha_poll->poll);

        $rcp_poll = RcpContestPoll::where('rcp_contest_pollster_id', '=', $rcp_contest_pollster_id)
            ->where('date_start', '=', $date_start_fixed)
            ->where('date_end', '=', $date_end_fixed)
            // ->where('percent_favor', '=', $micha_poll->v1)
            // ->where('percent_against', '=', $micha_poll->v2)
            ->first()
        ;
        
        if ( ! $rcp_poll) {
            d($micha_poll);
            return;
            $rcp_poll = new RcpContestPoll();
            $rcp_poll->rcp_contest_pollster_id = $rcp_contest_pollster_id;
            $rcp_poll->date_start = $date_start_fixed;
            $rcp_poll->date_end = $date_end_fixed;
            $rcp_poll->percent_favor = $micha_poll->v1;
            $rcp_poll->percent_against = $micha_poll->v2;
            $rcp_poll->save();
        }

        if ($micha_poll->type == 'add' || $micha_poll->type == 'update') {
            $rcp_poll->date_added_to_rcp_average = $day_of_timestamp;
            $rcp_poll->day_of_week_added_to_rcp = $weekday;
            $rcp_poll->save();
    
            $update_add = new RcpUpdateAdd();
            $update_add->rcp_update_id = $this->id;
            $update_add->rcp_contest_pollster_id = $rcp_contest_pollster_id;
            $update_add->rcp_contest_poll_id = $rcp_poll->id;
            $update_add->save();
        }

        $update_pollster = new RcpUpdatePollster();
        $update_pollster->rcp_update_id = $this->id;
        $update_pollster->rcp_contest_pollster_id = $rcp_contest_pollster_id;
        $update_pollster->rcp_contest_poll_id = $rcp_poll->id;
        $update_pollster->save();
    }

    /**
     * FOR MICHANIKOS IMPORTS ONLY.
     * Calculate our days.
     * Update a Poll with more information about when it was deleted.
     * Create RcpUpdateDrop related record and delete the UpdatePollster which came from the last update.
     */
    private function handleDeletePoll($micha_poll)
    {
        $weekday = date('l', strtotime($micha_poll->timestamp));
        $day_of_timestamp = date('Y-m-d', strtotime($micha_poll->timestamp));
        $rcp_contest_pollster_id = $this->findRcpContestPollster($micha_poll->poll);

        $update_pollster = $this->rcp_update_pollsters()
            ->where('rcp_contest_pollster_id', '=', $rcp_contest_pollster_id)
            ->first()
        ;

        if ( ! $update_pollster) {
            return;
        }
        $rcp_poll = $update_pollster->rcp_contest_poll;

        $first = new DateTime($rcp_poll->date_end);
        $last = new DateTime($day_of_timestamp);

        $rcp_poll->date_dropped_from_rcp_average = $day_of_timestamp;
        $rcp_poll->day_of_week_dropped_from_rcp = $weekday;
        $rcp_poll->age_of_poll_when_dropped_from_rcp = $first->diff($last)->format('%r%a');
        $rcp_poll->save();

        $update_drop = new RcpUpdateDrop();
        $update_drop->rcp_update_id = $this->id;
        $update_drop->rcp_contest_pollster_id = $rcp_contest_pollster_id;
        $update_drop->rcp_contest_poll_id = $rcp_poll->id;
        $update_drop->save();

        $update_pollster->delete();
    }
}
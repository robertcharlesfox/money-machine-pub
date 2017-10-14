<?php

use Sunra\PhpSimple\HtmlDomParser;

class RcpContestPollster extends Eloquent {

    private $latest_poll = '';
    private $second_latest_poll = '';
    private $avg_inclusion_value = array();
    private $trend_forecast = array();
    private $trend_st_dev = array();
    private $update_pollsters = '';
    private $gallup_proj_avg = '';
    private $rasmussen_proj_avg = '';
    private $gallup_st_dev = '';
    private $rasmussen_st_dev = '';

    private function getCachedValues()
    {
        // if (!$this->cached_values) {
            $this->cached_values = serialize($this->extractPollResult(['percent_favor', 'Clinton', 'Trump', 'Johnson', 'Stein', 'spread',]));
            $this->save();
        // }
        return unserialize($this->cached_values);
    }

    public function clearCachedValues(RcpContestPoll $poll)
    {
        $this->cached_values = '';
        $this->save();
    }

    public function valuesForAverage($include_baseline = true, $return_array = false, $update_bias = 0, $update_variance = 1.5, $column = 'spread')
    {
        $values = $this->getCachedValues();

        $early_result = $this->extractEarlyResult([$column]);
        if ($early_result[$column]) {
            $update_bias = 0;
            $update_variance = 0;
            $include_baseline = true;
        }

        $chance_updated = ($this->probability_updated*$this->probability_added) / 10000;
        $chance_dropped = $this->probability_dropped / 100;
        $chance_nothing = max(1 - $chance_updated - $chance_dropped, 0);

        if ($column == 'spread') {
            $result = ($this->projected_result && $this->projected_result != 0) ? $this->projected_result : $values['Clinton'] - $values['Trump'];
        } else {
            $result = ($this->projected_result && $this->projected_result != 0) ? $this->projected_result : $values[$column];
        }
        $baseline_text = $result . '/' . number_format($chance_nothing, 1);

        $update_result = $result + $update_bias;
        $update_range = number_format($update_result - $update_variance, 1) . ' - ' . number_format($update_result + $update_variance, 1);
        $marker = $include_baseline ? ' *** ' : '';
        $update_text = $chance_updated ? $update_range . '/' . number_format($chance_updated, 1) . $marker : '';

        if ($return_array) {
            return [
                $column => $result,
                'update_result' => $update_result,
                'chance_nothing' => $chance_nothing,
                'chance_updated' => $chance_updated,
                'update_variance' => $update_variance,
            ];
        }
        return $include_baseline ? $update_text . $baseline_text : $update_text;
    }

    // public function netValue($include_baseline = true, $return_array = false, $update_variance = 1.5)
    // {
    //     $values = $this->getCachedValues();

    //     $chance_updated = ($this->probability_updated*$this->probability_added) / 10000;
    //     $chance_dropped = $this->probability_dropped / 100;
    //     $chance_nothing = 1 - $chance_updated - $chance_dropped;

    //     $spread = ($this->projected_result && $this->projected_result != 0) ? $this->projected_result : $values['Clinton'] - $values['Trump'];
    //     $baseline_text = $spread . '/' . number_format($chance_nothing, 1);

    //     // $update_variance = 1.5;
    //     $update_range = number_format($spread - $update_variance, 1) . ' - ' . number_format($spread + $update_variance, 1);
    //     $marker = $include_baseline ? ' *** ' : '';
    //     $update_text = $chance_updated ? $update_range . '/' . number_format($chance_updated, 1) . $marker : '';

    //     if ($return_array) {
    //         return [
    //             'chance_nothing' => $chance_nothing,
    //             'spread' => $spread,
    //             'chance_updated' => $chance_updated,
    //             'update_variance' => $update_variance,
    //         ];
    //     }
    //     return $include_baseline ? $update_text . $baseline_text : $update_text;
    // }

    public function extractOldPolls($candidates)
    {
        $polls = $this->rcp_contest_polls()
                    ->orderBy('mark_as_old', 'asc')
                    ->orderBy('date_end', 'desc')
                    ->orderBy('id', 'desc')
                    ->get()
                    ->take(6);
        $polls_data = [];
        foreach ($polls as $poll) {
            $poll_data['added'] = $poll->rcp_update_add ? $poll->last_add->rcp_update->local_rcp_timestamp() : '';
            $poll_data['text_date_range'] = $poll->text_date_range;
            foreach ($candidates as $candidate) {
                $poll_data[$candidate] = $poll->$candidate;
            }
            $poll_data['dropped'] = $poll->rcp_update_drop ? $poll->last_drop->rcp_update->local_rcp_timestamp() : '';
            $poll_data['age'] = $poll->age_of_poll_when_dropped_from_rcp . ' / ' . $poll->length_in_average;
            $poll_data['id'] = $poll->id;
            $poll_data['mark_as_old'] = $poll->mark_as_old;
            $polls_data[] = $poll_data;
        }

        return $polls_data;
    }

    public function extractEarlyResult($candidates)
    {
        $early_results = [];
        foreach ($candidates as $candidate) {
            $earlyFieldName = 'early_' . $candidate;
            $early_results[$candidate] = $this->$earlyFieldName;
        }
        return $early_results;
    }

    public function extractPollResult($candidates)
    {
        $early_results = $this->extractEarlyResult($candidates);
        $poll = $this->latest_poll();
        $poll = $poll ? $poll : new RcpContestPoll();
        $po_array = [];
        foreach ($candidates as $name) {
            $po_array[$name] = $early_results[$name] ? $early_results[$name]: $poll->$name;
        }
        $po_array['id'] = $poll->id;
        $po_array['dates'] = $poll->text_date_range;
        $po_array['age'] = $poll->current_poll_age . ' / ' . $poll->current_days_in;
        return $po_array;
    }


    public function getShowAsFinalAttribute()
    {
        if ($this->probability_added == 0 &&
            $this->probability_updated == 0 &&
            $this->probability_dropped == 0
            ) {
            return true;
        }
        return false;
    }

    public function getShowAsLikelyUpdateAttribute()
    {
        if ($this->probability_updated > 20) {
            return true;
        }
        return false;
    }

    public function getShowAsLikelyDropAttribute()
    {
        if ($this->probability_dropped > 50) {
            return true;
        }
        return false;
    }

    public function getShowAsPossibleDropAttribute()
    {
        if ($this->probability_dropped > 0) {
            return true;
        }
        return false;
    }

    public function pi_contest()
    {
        return $this->belongsTo(PiContest::class);
    }

    public function rcp_contest_polls()
    {
        return $this->hasMany(RcpContestPoll::class);
    }

    public function micha_obama_scrapes()
    {
        return $this->hasMany(MichaObamaScrape::class);
    }

    public function rcp_drop_trades()
    {
        return $this->hasMany(RcpDropTrade::class);
    }

    public function rcp_add_trades()
    {
        return $this->hasMany(RcpAddTrade::class);
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
        if ( ! $this->update_pollsters) {
            $this->update_pollsters = $this->hasMany(RcpUpdatePollster::class);
        }
        return $this->update_pollsters;
    }

    public function reactivate()
    {
        $this->new_poll_update_text = '';
        $this->save();
    }

    public function saveContestPollster($input)
    {
        $this->un_included_actual_result = isset($input['un_included_actual_result']) ? $input['un_included_actual_result'] : '';
        $this->projected_result = isset($input['projected_result']) ? $input['projected_result'] : '';
        $this->is_likely_final_for_week = isset($input['is_likely_final_for_week']) ? $input['is_likely_final_for_week'] : '';
        $this->is_likely_dropout = isset($input['is_likely_dropout']) ? $input['is_likely_dropout'] : '';
        $this->is_likely_addition = isset($input['is_likely_addition']) ? $input['is_likely_addition'] : '';
        $this->debate_eligible_poll = isset($input['debate_eligible_poll']) ? $input['debate_eligible_poll'] : '';
        $this->keep_scraping = isset($input['keep_scraping']) ? $input['keep_scraping'] : '';
        $this->auto_trade_updates = isset($input['auto_trade_updates']) ? $input['auto_trade_updates'] : '';
        $this->comments = isset($input['comments']) ? $input['comments'] : '';
        $this->next_poll_expected = isset($input['next_poll_expected']) ? $input['next_poll_expected'] : '';
        $this->save();
    }

    public function saveContestPollsterResult($input)
    {
        if ($this->pi_contest_id == 12) {
            $this->early_Clinton = $input['early_Clinton'];
            $this->early_Sanders = $input['early_Sanders'];
            $this->early_OMalley = $input['early_OMalley'];
        }
        else {
            $this->early_Trump = $input['early_Trump'];
            $this->early_Carson = $input['early_Carson'];
            $this->early_Rubio = $input['early_Rubio'];
            $this->early_Cruz = $input['early_Cruz'];
            $this->early_Bush = $input['early_Bush'];
            $this->early_Fiorina = $input['early_Fiorina'];
            $this->early_Paul = $input['early_Paul'];
            $this->early_Christie = $input['early_Christie'];
        }
        $this->save();
    }

    public function clearPollsterBooleans()
    {
        $this->early_Clinton = '';
        $this->early_Trump = '';
        $this->early_Johnson = '';
        $this->early_Stein = '';
        $this->early_spread = '';
        $this->un_included_actual_result = '';
        $this->projected_result = '';
        $this->new_poll_update_text = '';

        if ($this->update_frequency != 'daily') {
            $this->is_likely_final_for_week = 1;
            $this->probability_updated = 0;
        }
        $this->probability_added = 0;
        $this->probability_dropped = 0;
        $this->is_likely_dropout = 0;
        $this->is_likely_addition = 0;
        $this->save();
    }

    public function sorted_polls()
    {
        return $this->rcp_contest_polls()
            ->orderBy('mark_as_old', 'asc')
            ->orderBy('date_end', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function latest_poll()
    {
        if ( ! $this->latest_poll) {
            $this->latest_poll = RcpContestPoll::where('rcp_contest_pollster_id', '=', $this->id)
                                            ->where('mark_as_old', '=', 0)
                                            ->orderBy('date_end', 'desc')
                                            ->orderBy('id', 'desc')
                                            ->first();
        }
        return $this->latest_poll;
    }

    public function second_latest_poll()
    {
        if ( ! $this->second_latest_poll) {
            $this->second_latest_poll = RcpContestPoll::where('rcp_contest_pollster_id', '=', $this->id)
                ->where('mark_as_old', '=', 0)
                ->orderBy('date_end', 'desc')
                ->orderBy('id', 'desc')
                ->take(2)
                ->get()
                ->last();
        }
        return $this->second_latest_poll;
    }

    public function first_poll()
    {
        return RcpContestPoll::where('rcp_contest_pollster_id', '=', $this->id)
                    ->orderBy('date_end', 'asc')
                    ->first();
    }

    /**
     * If last poll ends Wednesday, use 2 numbers of actual daily data.
     * If Tuesday, use 1 daily and the 10-day average.
     * On all other days, just use 10-day average.
     * Same formula for the next 3 functions.
     * This is obviously off if the week "ends" on something other than a Friday.
     */
    public function gallupProjAvg()
    {
        if ( ! $this->gallup_proj_avg) {
            $average = $this->rcp_contest_polls()->where('gallup_daily_estimate', '>', 0)->orderBy('id', 'desc')->take(10)->get()->pluck('gallup_daily_estimate')->avg();
            $implied1 = $this->latest_poll()->gallupBestGuessOrActual();
            $implied2 = $this->second_latest_poll()->gallupBestGuessOrActual();
            switch (date('l', strtotime($this->latest_poll()->date_end))) {
                case 'Wednesday':
                    $this->gallup_proj_avg = ($implied1 + $implied2 + $average) / 3;
                    break;
                
                case 'Tuesday':
                    $this->gallup_proj_avg = ($implied1 + $average + $average) / 3;
                    break;
                
                default:
                    $this->gallup_proj_avg = $average;
                    break;
            }
        }
        return number_format($this->gallup_proj_avg, 1);
    }

    public function rasmussenProjAvg()
    {
        if ( ! $this->rasmussen_proj_avg) {
            $average = $this->rcp_contest_polls()->where('rasmussen_daily_estimate', '>', 0)->orderBy('id', 'desc')->take(10)->get()->pluck('rasmussen_daily_estimate')->avg();
            $implied1 = $this->latest_poll()->gallupBestGuessOrActual();
            $implied2 = $this->second_latest_poll()->gallupBestGuessOrActual();
            switch (date('l', strtotime($this->latest_poll()->date_end))) {
                case 'Wednesday':
                    $this->rasmussen_proj_avg = ($implied1 + $implied2 + $average) / 3;
                    break;
                
                case 'Tuesday':
                    $this->rasmussen_proj_avg = ($implied1 + $average + $average) / 3;
                    break;
                
                default:
                    $this->rasmussen_proj_avg = $average;
                    break;
            }
        }
        return number_format($this->rasmussen_proj_avg, 1);
    }

    public function gallupStDev()
    {
        if ( ! $this->gallup_st_dev) {
            $recent_dailies = $this->rcp_contest_polls()->where('gallup_daily_estimate', '>', 0)->orderBy('id', 'desc')->take(10)->get()->pluck('gallup_daily_estimate')->toArray();
            $dev = $this->standard_deviation($recent_dailies);
            switch (date('l', strtotime($this->latest_poll()->date_end))) {
                case 'Wednesday':
                    $this->gallup_st_dev = $dev / 3;
                    break;
                
                case 'Tuesday':
                    $this->gallup_st_dev = ($dev + $dev) / 3;
                    break;
                
                default:
                    $this->gallup_st_dev = $dev;
                    break;
            }
        }
        return number_format($this->gallup_st_dev, 1);
    }

    public function rasmussenStDev()
    {
        if ( ! $this->rasmussen_st_dev) {
            $recent_dailies = $this->rcp_contest_polls()->where('rasmussen_daily_estimate', '>', 0)->orderBy('id', 'desc')->take(10)->get()->pluck('rasmussen_daily_estimate')->toArray();
            $dev = $this->standard_deviation($recent_dailies);
            switch (date('l', strtotime($this->latest_poll()->date_end))) {
                case 'Wednesday':
                    $this->rasmussen_st_dev = $dev / 3;
                    break;
                
                case 'Tuesday':
                    $this->rasmussen_st_dev = ($dev + $dev) / 3;
                    break;
                
                default:
                    $this->rasmussen_st_dev = $dev;
                    break;
            }
        }
        return number_format($this->rasmussen_st_dev, 1);
    }

    /**
     * For approval ratings, the pollster history is more important. For candidates, it's the group.
     * @todo: add a calculation of linear trend to the forecast average
     */
    public function trendForecast($recent_polls, $candidate = '')
    {
        if ($candidate) {
            if ( ! isset($this->trend_forecast[$candidate])) {
                $poll_values = array_reverse($recent_polls->pluck($candidate)->all());
                $regression = $this->doRegression($poll_values);
                $recent_average = (array_sum($poll_values) / count($poll_values)) + $regression['m'];
                $latest = $this->latest_poll()->$candidate;
                $this->trend_forecast[$candidate] = number_format(((($recent_average * 2) + $latest) / 3), 1);
            }
        }
        elseif ( ! $this->trend_forecast) {
            $poll_values = array_reverse($recent_polls->pluck('percent_favor')->all());
            $regression = $this->doRegression($poll_values);
            $recent_average = (array_sum($poll_values) / count($poll_values)) + $regression['m'];
            $latest = $this->latest_poll()->percent_favor;
            $this->trend_forecast = number_format((($recent_average + ($latest * 2)) / 3), 1);
        }

        return $candidate ? $this->trend_forecast[$candidate] : $this->trend_forecast;
    }

    private function doRegression($poll_values)
    {
        $x_coords = array();
        for ($i=0; $i < count($poll_values); $i++) { 
            $x_coords[] = $i;
        }
        return $this->linear_regression($x_coords, $poll_values);
    }

    /**
     * https://richardathome.wordpress.com/2006/01/25/a-php-linear-regression-function/
     * linear regression function
     * @param $x array x-coords
     * @param $y array y-coords
     * @returns array() m=>slope, b=>intercept
     */
    function linear_regression($x, $y) {

      // calculate number points
      $n = count($x);
      
      // ensure both arrays of points are the same size
      if ($n != count($y)) {

        trigger_error("linear_regression(): Number of elements in coordinate arrays do not match.", E_USER_ERROR);
      
      }

      // calculate sums
      $x_sum = array_sum($x);
      $y_sum = array_sum($y);

      $xx_sum = 0;
      $xy_sum = 0;
      
      for($i = 0; $i < $n; $i++) {
      
        $xy_sum+=($x[$i]*$y[$i]);
        $xx_sum+=($x[$i]*$x[$i]);
        
      }
      
      // calculate slope
      $m = (($n * $xy_sum) - ($x_sum * $y_sum)) / (($n * $xx_sum) - ($x_sum * $x_sum));
      
      // calculate intercept
      $b = ($y_sum - ($m * $x_sum)) / $n;
        
      // return result
      return array("m"=>$m, "b"=>$b);

    }

    /**
     * To capture variance, look at all recent polls for this contest, then include the pollster's latest (twice).
     */
    public function trendStDev($recent_polls, $candidate = '')
    {
        if ($candidate) {
            if ( ! isset($this->trend_st_dev[$candidate])) {
                $poll_values = $recent_polls->pluck($candidate)->all();
                $poll_values[] = $this->latest_poll()->$candidate;
                $poll_values[] = $this->latest_poll()->$candidate;
                $this->trend_st_dev[$candidate] = number_format($this->standard_deviation($poll_values), 1);
            }
        }
        elseif ( ! $this->trend_st_dev) {
            $poll_values = $recent_polls->pluck('percent_favor')->all();
            $poll_values[] = $this->latest_poll()->percent_favor;
            $poll_values[] = $this->latest_poll()->percent_favor;
            $this->trend_st_dev = number_format($this->standard_deviation($poll_values), 1);
        }

        return $candidate ? $this->trend_st_dev[$candidate] : $this->trend_st_dev;
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

    public function showAsOtherPollster($update)
    {
        if ($this->latest_poll() &&
            $this->isNotOutOfDate() &&
            ($this->rcp_update_pollsters()->count() == 0 || 
            $this->rcp_update_pollsters()->orderBy('id', 'desc')->first()->rcp_update_id != $update->id) 
            ) {
            return true;
        }
        return false;
    }

    public function avgInclusionValue($candidate = '')
    {
        if ($candidate) {
            if ( ! isset($this->avg_inclusion_value[$candidate])) {
                $earlyCandidate = ($candidate == "O'Malley") ? 'OMalley' : $candidate;
                $earlyFieldName = 'early_' . $earlyCandidate;
                $candidate_value = $this->$earlyFieldName ? $this->$earlyFieldName : $this->latest_poll()->$candidate;
                $this->avg_inclusion_value[$candidate] = $candidate_value;
            }
        }
        elseif ( ! $this->avg_inclusion_value) {
            $this->avg_inclusion_value = $this->un_included_actual_result ? $this->un_included_actual_result : $this->latest_poll()->percent_favor;
        }

        return $candidate ? $this->avg_inclusion_value[$candidate] : $this->avg_inclusion_value;
    }

    public function daysPerPoll()
    {
        $first = new DateTime($this->first_poll()->date_end);
        $last = new DateTime($this->latest_poll()->date_end);
        return (int) ($first->diff($last)->format('%r%a') / $this->rcp_contest_polls->count());
    }

    public function isNotOutOfDate()
    {
        $last = new DateTime($this->latest_poll()->date_end);
        $today = new DateTime();
        if ($last > $today->sub(new DateInterval('P500D'))) {
            return true;
        }
        return false;
    }

    public function projectedUpdateShort()
    {
        $next = strtotime($this->latest_poll()->date_end . ' +' . $this->daysPerPoll() . 'days');
        return date('m-d', $next);
    }

    public function scrapeQuinHtml()
    {
        $url = 'http://www.quinnipiac.edu/news-and-events/quinnipiac-university-poll/';
        $scraper = new Scraper($url);
        $dom = HtmlDomParser::str_get_html($scraper->html);

        $releases = $dom->find('article[class=mainColumn] p');
        $releaseIdComparator = 0;
        foreach ($releases as $release) {
            $fullTopline = $release->plaintext;
            $link = $release->find('a', 0);
            if ( ! $link) {
                break;
            }
            $linkDescription = $link->plaintext;
            $releaseUrl = $link->href;
            $releaseId = substr($releaseUrl, strpos($releaseUrl, 'ID=') + 3);
            if ($releaseId > $this->last_scrape_link) {
                // This poll has a higher ID than the last poll of interest.
                // It's a national poll.
                if (stristr($fullTopline, 'national poll')) {
                    // It's a new poll announcement.
                    if (stristr($fullTopline, 'Results of a')) {
                        if ($this->last_scrape_other != 'announcement') {
                            $this->last_scrape_other = 'announcement';
                            $this->selenium_url = $releaseUrl;
                            $this->save();
                            // By not saving the update text, it will continue scraping again.
                            $this->new_poll_update_text = "new National Poll coming \n \n " . $fullTopline;
                        }
                    }
                    // It's an actual poll.
                    else {
                        $this->new_poll_update_text = "new National Poll is out \n \n " . $releaseUrl;
                        $this->last_scrape_title = $fullTopline;
                        $this->last_scrape_link = $releaseId;
                        $this->last_scrape_other = '';
                        $this->selenium_url = $releaseUrl;
                        $this->save();
                    }
                }
                // There are gaps in the numbering system.
                elseif (($releaseId + 1) < $releaseIdComparator) {
                    if ($this->last_scrape_other != 'announcement' && $this->last_scrape_other != 'gaps') {
                        $this->last_scrape_other = 'gaps';
                        $this->save();
                        // By not saving the update text, it will continue scraping again.
                        $this->new_poll_update_text = "gaps in Quin poll list";
                    }
                }
                $releaseIdComparator = $releaseId;
            }
        }

        $dom->clear();
        unset($dom);
    }

    // public function scrapeQuinRss()
    // {
    //     $url = 'http://www.quinnipiac.edu/news-and-events/quinnipiac-university-poll/rss-polling-aggregate/';
    //     $scraper = new Scraper($url);
    //     $xml = simplexml_load_string($scraper->html);
    //     $item = $xml->channel->item[0];
    //     $title = $item->title;
    //     if (stristr($title, 'National:') || stristr($title, 'national poll')) {
    //         if ( ! stristr($title, $this->scrape_instructions)) {
    //             $this->new_poll_update_text = $title->__toString() . " \n \n " . $item->link->__toString();
    //             $this->scrape_instructions = substr($title->__toString(), 0, 30);
    //             $this->last_scrape_link = $item->link->__toString();
    //             $this->selenium_url = $item->link->__toString();
    //             $this->last_scrape_title = $title->__toString();
    //             $this->save();
    //         }
    //     }
    // }

}

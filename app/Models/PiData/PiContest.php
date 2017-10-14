<?php

class PiContest extends Eloquent
{
    public $bracketContests = [1,3,8,187,220,225,224,227,221,];
    public $CvTContests = [187,220,221,222,223,224,225,227,228,229,230,231,232,233,234,235,];

    public function getCalculatedSpreadAttribute()
    {
        return $this->last_rcp_update()->Clinton - $this->last_rcp_update()->Trump;
    }

    /**
     * get the relevant current questions
     */
    public function getCurrentContestValues()
    {
        $questions = $this->getCurrentPiQuestions();
        $pollsters = $this->getCurrentPollsters();
        $projections['market'] = $this->calculateRcpAverageProjections($pollsters, true);
        $projections['straight'] = $this->calculateRcpAverageProjections($pollsters, false);
        $valuations = $this->calculateQuestionValuations($questions, $projections);
        return ['questions' => $valuations, 'projections' => $projections, 'pollsters' => $pollsters,];
    }

    public function getCurrentPiQuestions()
    {
        switch ($this->id) {
            case in_array($this->id, $this->bracketContests):
                return $this->getLatestContest()
                    ->pi_questions()
                    ->orderBy('fundraising_low', 'desc')
                    ->get();
            
            default:
                return $this->pi_questions();
        }
    }

    public function getLatestContest()
    {
        $contest = PiContest::where('category', '=', $this->getContestMagicStrings('category'))
            ->orderBy('id', 'desc')
            ->first();
        return $contest ? $contest : new PiContest;
    }

    public function getContestMagicStrings($category_or_column)
    {
        if ($category_or_column == 'category') {
            switch ($this->id) {
                case 1:
                    return 'obama';
                case 3:
                    return 'doc';
                case 187:
                    return 'polls_clinton_vs_trump';
                case 220:
                    return 'polls_clinton_vs_trump_pa';
                case 221:
                    return 'polls_clinton_vs_trump_oh';
                case 224:
                    return 'polls_clinton_vs_trump_nc';
                case 225:
                    return 'polls_clinton_vs_trump_fl';
                case 227:
                    return 'polls_clinton_vs_trump_nv';
                case 8:
                    return 'congress';
            }
        } elseif ($category_or_column == 'column') {
            switch ($this->id) {
                case 1:  case 3:  case 8:
                    return 'percent_favor';
                case in_array($this->id, $this->CvTContests):
                    return 'spread';
                case 189:
                    return 'Johnson';
            }
        } elseif ($category_or_column == 'extra_columns') {
            switch ($this->id) {
                case 1:  case 3:  case 8:
                    return ['percent_favor'];
                case in_array($this->id, $this->CvTContests): case 189:
                    return [
                        'Clinton',
                        'Trump',
                        'Johnson',
                        'Stein',
                        'spread',
                    ];
            }
        } else {
            Log::info('wrong place');
            die('wrong place');
        }
    }

    public function getCurrentPollsters($pollster_data = [])
    {
        $rcp_update_pollsters = $this->last_rcp_update()->rcp_update_pollsters()->get();
        $rcp_contest_pollster_ids_in_rcp_average = $rcp_update_pollsters->pluck('rcp_contest_pollster_id')->toArray();
        $all_rcp_contest_pollsters = $this->rcp_contest_pollsters()->orderBy('probability_updated', 'desc')->get();

        foreach ($rcp_update_pollsters as $pollster) {
            $pollster_data['rcp_average'][] = $this->extractPollsterArray($pollster->rcp_contest_pollster, true);
        }

        $pollster_data['others'] = [];
        foreach ($all_rcp_contest_pollsters as $pollster) {
            if (!in_array($pollster->id, $rcp_contest_pollster_ids_in_rcp_average)) {
                if ($pollster->showAsOtherPollster($this->last_rcp_update())) {
                    $pollster_data['others'][] = $this->extractPollsterArray($pollster, false);
                }
            }
        }

        return $pollster_data;
    }

    private function extractPollsterArray(RcpContestPollster $pollster, $include_baseline)
    {
        $columns = $this->getContestMagicStrings('extra_columns');
        $column = $this->getContestMagicStrings('column');
        return array(
            'pollster' => $pollster,
            'latest_result' => $pollster->extractPollResult($columns),
            'early_result' => $pollster->extractEarlyResult($columns),
            'latest_polls' => $pollster->extractOldPolls($columns),
            'values_for_average' => $pollster->valuesForAverage($include_baseline, false, $this->implied_bias, $this->implied_variance, $column),
        );
    }

    public function calculateRcpAverageProjections($pollsters, $use_market_implied)
    {
        $high_values = [];
        $low_values = [];
        $avg_values = [];
        $weightings = [];
        $column = $this->getContestMagicStrings('column');
        $implied_bias = $use_market_implied ? $this->implied_bias : 0;
        $implied_variance = $use_market_implied ? $this->implied_variance : 1.5;

        foreach ($pollsters['rcp_average'] as $pollster) {
            $pollster_values = $pollster['pollster']->valuesForAverage(true, true, $implied_bias, $implied_variance, $column);

            if ($pollster_values['chance_nothing'] > 0) {
                $avg_values[] = $pollster_values[$column] * $pollster_values['chance_nothing'];
                $weightings[] = $pollster_values['chance_nothing'];
            }

            if ($pollster_values['chance_updated'] > 0) {
                $high_values[] = ($pollster_values['update_result'] + $pollster_values['update_variance']) * $pollster_values['chance_updated'];
                $low_values[] = ($pollster_values['update_result'] - $pollster_values['update_variance']) * $pollster_values['chance_updated'];
                $weightings[] = $pollster_values['chance_updated'];
            }
        }

        foreach ($pollsters['others'] as $other) {
            $pollster_values = $other['pollster']->valuesForAverage(false, true, $implied_bias, $implied_variance, $column);
            if ($pollster_values['chance_updated'] > 0) {
                $high_values[] = ($pollster_values['update_result'] + $pollster_values['update_variance']) * $pollster_values['chance_updated'];
                $low_values[] = ($pollster_values['update_result'] - $pollster_values['update_variance']) * $pollster_values['chance_updated'];
                $weightings[] = $pollster_values['chance_updated'];
            }
        }
        
        $sum_low = array_sum($low_values) + array_sum($avg_values);
        $sum_high = array_sum($high_values) + array_sum($avg_values);
        $sum_weightings = array_sum($weightings);

        $values['low_end'] = number_format($sum_low/$sum_weightings, 2);
        $values['high_end'] = number_format($sum_high/$sum_weightings, 2);
        $values['average'] = number_format(($values['low_end'] + $values['high_end'])/2, 2);
        $values['variance'] = number_format(($values['high_end'] - $values['average']), 2);
        $values['all_inclusive'] = $values['low_end'] . ' - ' . $values['high_end'];
        // d($sum_low, $sum_high, $sum_weightings, $low_values, $high_values, $avg_values, $weightings, $values);
        return $values;
    }

    public function calculateQuestionValuations($questions, $projections)
    {
        if (!$questions->count()) {
            return $questions;
        }
        $last_update = $this->last_rcp_update();
        switch ($this->id) {
            case in_array($this->id, $this->bracketContests):
                $questions[0]->chance_to_win = (int) $last_update->tvc_valuation($questions[0]->fundraising_low, 99.9, $projections['market']);
                $questions[0]->save();
                $questions[1]->chance_to_win = (int) $last_update->tvc_valuation($questions[1]->fundraising_low, $questions[0]->fundraising_low, $projections['market']);
                $questions[1]->save();
                $questions[2]->chance_to_win = (int) $last_update->tvc_valuation($questions[2]->fundraising_low, $questions[1]->fundraising_low, $projections['market']);
                $questions[2]->save();
                $questions[3]->chance_to_win = (int) $last_update->tvc_valuation($questions[3]->fundraising_low, $questions[2]->fundraising_low, $projections['market']);
                $questions[3]->save();
                if ($questions->count() == 5) {
                    $questions[4]->chance_to_win = (int) $last_update->tvc_valuation(-99.9, $questions[3]->fundraising_low, $projections['market']);
                    $questions[4]->save();
                } elseif ($questions->count() == 6) {
                    $questions[4]->chance_to_win = (int) $last_update->tvc_valuation($questions[4]->fundraising_low, $questions[3]->fundraising_low, $projections['market']);
                    $questions[4]->save();
                    $questions[5]->chance_to_win = (int) $last_update->tvc_valuation(-99.9, $questions[4]->fundraising_low, $projections['market']);
                    $questions[5]->save();
                }
                return $questions;
        }
    }


    public function updateValues($candidate = 'spread')
    {
        $high_values = [];
        $low_values = [];
        $avg_values = [];
        $weightings = [];

        $update_pollsters = $this->last_rcp_update()->rcp_update_pollsters()->get();
        foreach ($update_pollsters as $update_pollster) {
            $pollster_values = $update_pollster->rcp_contest_pollster->netValue(true, true);

            if ($pollster_values['chance_nothing'] > 0) {
                $avg_values[] = $pollster_values[$candidate] * $pollster_values['chance_nothing'];
                $weightings[] = $pollster_values['chance_nothing'];
            }

            // NEW: add implied bias calculation here. Effectively a "prediction" of which way polls are headed.
            if ($pollster_values['chance_updated'] > 0) {
                $high_values[] = ($this->implied_bias + $pollster_values[$candidate] + $pollster_values['update_variance']) * $pollster_values['chance_updated'];
                $low_values[] = ($this->implied_bias + $pollster_values[$candidate] - $pollster_values['update_variance']) * $pollster_values['chance_updated'];
                $weightings[] = $pollster_values['chance_updated'];
            }
        }

        $other_pollsters = $this->getNonUpdatePollsters();
        foreach ($other_pollsters as $other) {
            $pollster_values = $other['pollster']->netValue(false, true);
            // NEW: add implied bias calculation here. Effectively a "prediction" of which way polls are headed.
            if ($pollster_values['chance_updated'] > 0) {
                $high_values[] = ($this->implied_bias + $pollster_values[$candidate] + $pollster_values['update_variance']) * $pollster_values['chance_updated'];
                $low_values[] = ($this->implied_bias + $pollster_values[$candidate] - $pollster_values['update_variance']) * $pollster_values['chance_updated'];
                $weightings[] = $pollster_values['chance_updated'];
            }
        }
        
        $sum_low = array_sum($low_values) + array_sum($avg_values);
        $sum_high = array_sum($high_values) + array_sum($avg_values);
        $sum_weightings = array_sum($weightings);

        $values['low_end'] = number_format($sum_low/$sum_weightings, 2);
        $values['high_end'] = number_format($sum_high/$sum_weightings, 2);
        $values['average'] = number_format(($values['low_end'] + $values['high_end'])/2, 2);
        $values['variance'] = number_format(($values['high_end'] - $values['average']), 2);
        $values['all_inclusive'] = $values['low_end'] . ' - ' . $values['high_end'];
        return $values;
    }

    /**
     * However, we need the actual PiContest with related contracts.
     * That PiContest is found by being active and being in a matching category.
     * Then we pull the relevant RCP average numbers from the question ticker.
     */
    public function getContestPollingValues($category, $include_id = false)
    {
        $values = array();
        $polling_contests = PiContest::where('active', '=', 1)
                            ->where('category', '=', $category)
                            ->orderBy('id', 'desc')
                            ->get();
        if ($polling_contests->count() > 0) {
            $polling_questions = $polling_contests->first()->pi_questions;
            foreach ($polling_questions as $question) {
                if ($include_id) {
                    $question_values = array();
                    $question_values['id'] = $question->id;
                    $question_values['threshold'] = $question->fundraising_low;
                    $question_values['question'] = $question;
                    $values[] = $question_values;
                }
                else {
                    $values[] = $question->fundraising_low;
                }
            }
        }
        if ($include_id) {
            $values = array_values(array_sort($values, function ($value) {
                return $value['threshold'];
            }));
            $values = array_reverse($values);
        }
        else {
            arsort($values);
        }
        return $values;
    }

    /**
     * Approval thresholds for Obama contracts come from the related contracts.
     * Other Approval markets are set, for now, via Admin interface.
     */
    public function evaluate()
    {
        $values = array();
        $last_update = $this->last_rcp_update();
        switch ($this->id) {
            case 1:
                $contest_values = $this->contestValuesObama(true);
                if (count($contest_values) == 5) {
                    $values[$contest_values[0]['id']] = (int) $last_update->valuation($contest_values[0]['threshold'], 99.9);
                    $values[$contest_values[1]['id']] = (int) $last_update->valuation($contest_values[1]['threshold'], $contest_values[0]['threshold']);
                    $values[$contest_values[2]['id']] = (int) $last_update->valuation($contest_values[2]['threshold'], $contest_values[1]['threshold']);
                    $values[$contest_values[3]['id']] = (int) $last_update->valuation($contest_values[3]['threshold'], $contest_values[2]['threshold']);
                    $values[$contest_values[4]['id']] = (int) $last_update->valuation(0.1, $contest_values[3]['threshold']);
                }
                break;
            
            case 187:
                $contest_values = $this->getContestPollingValues('polls_clinton_vs_trump', true);
                $update_values = $this->updateValues();
                if (count($contest_values) == 5) {
                    $values[] = [
                        'id' => $contest_values[0]['id'],
                        'value' => (int) $last_update->tvc_valuation($contest_values[0]['threshold'], 99.9, $update_values),
                    ];
                    $values[] = [
                        'id' => $contest_values[1]['id'],
                        'value' => (int) $last_update->tvc_valuation($contest_values[1]['threshold'], $contest_values[0]['threshold'], $update_values),
                    ];
                    $values[] = [
                        'id' => $contest_values[2]['id'],
                        'value' => (int) $last_update->tvc_valuation($contest_values[2]['threshold'], $contest_values[1]['threshold'], $update_values),
                    ];
                    $values[] = [
                        'id' => $contest_values[3]['id'],
                        'value' => (int) $last_update->tvc_valuation($contest_values[3]['threshold'], $contest_values[2]['threshold'], $update_values),
                    ];
                    $values[] = [
                        'id' => $contest_values[4]['id'],
                        'value' => (int) $last_update->tvc_valuation(-99, $contest_values[3]['threshold'], $update_values),
                    ];
                    foreach ($values as $value) {
                        $question = PiQuestion::find($value['id']);
                        $question->chance_to_win = $value['value'];
                        $question->save();
                    }
                }
                break;
            
            default:
                $values[$this->approval_threshold_1] = $last_update->valuation($this->approval_threshold_1);
                break;
        }
        return $values;
    }

    public function getProjectionPollsters($candidates)
    {
        $pollster_data = [];
        $projection_pollsters = $this->last_rcp_update()->rcp_update_pollsters()->get();

        foreach ($projection_pollsters as $pollster) {
            $pollster = $pollster->rcp_contest_pollster;
            $pol = array(
                'pollster' => $pollster,
                'latest_result' => $pollster->extractPollResult($candidates),
                'early_result' => $pollster->extractEarlyResult($candidates),
                'latest_polls' => $pollster->extractOldPolls($candidates),
            );
            
            $pollster_data[] = $pol;
        }
        return $pollster_data;
    }

    public function getNonUpdatePollsters($candidates = [])
    {
        $last = $this->last_rcp_update()->rcp_update_pollsters->pluck('rcp_contest_pollster_id')->toArray();
        $all = $this->rcp_contest_pollsters()->orderBy('probability_updated', 'desc')->get();
        $non_update = [];

        foreach ($all as $pollster) {
            if (!in_array($pollster->id, $last)) {
                $pol['pollster'] = $pollster;
                $pol['latest_result'] = $candidates ? $pollster->extractPollResult($candidates) : '';
                $pol['early_result'] = $candidates ? $pollster->extractEarlyResult($candidates) : '';
                $pol['latest_polls'] = $pollster->extractOldPolls($candidates);
                
                $non_update[] = $pol;
            }
        }
        return $non_update;
    }

    /**
     * $this is the Obama contest.
     * However, we need the actual PiContest with related contracts.
     * That PiContest is found by being active and having a string in its name.
     * Then we pull the relevant RCP average numbers from the question ticker.
     */
    public function contestValuesObama($include_id = false)
    {
        $values = array();
        if ($this->id == 1) {
            $active_contests = PiContest::where('active', '=', 1)
                                ->orderBy('id', 'desc')
                                ->get();
            $obama_contests = $active_contests->filter(function ($contest) {
                return stristr($contest->name, 'Obama Approval');
            });
            if ($obama_contests->count() > 0) {
                $obama_questions = $obama_contests->first()->pi_questions;
                foreach ($obama_questions as $question) {
                    if ($include_id) {
                        $question_values = array();
                        $question_values['id'] = $question->id;
                        $question_values['threshold'] = substr($question->question_ticker, 0, 3) / 10;
                        $values[] = $question_values;
                    }
                    else {
                        $values[] = substr($question->question_ticker, 0, 3) / 10;
                    }
                }
            }
            if ($include_id) {
                $values = array_values(array_sort($values, function ($value) {
                    return $value['threshold'];
                }));
                $values = array_reverse($values);
            }
            else {
                arsort($values);
            }
        }
        return $values;
    }

    private function formatAverages($poll_values)
    {
        if (count($poll_values)) {
            return number_format((array_sum($poll_values) / count($poll_values)), 2) . ' - ' . count($poll_values) . ' polls';
        }
        return 'No Polls';
    }

    public function getCompetitionTotalAttribute()
    {
        return $this->getCurrentPiQuestions()->sum('chance_to_win');
    }

    public function getCompetitionYesTotalAttribute()
    {
        return $this->getCurrentPiQuestions()->sum('cache_market_support_yes_side_price');
    }

    public function getCompetitionNoTotalAttribute()
    {
        $total = $this->getCurrentPiQuestions()->sum('cache_market_support_no_side_price');
        $average = $total / ($this->getCurrentPiQuestions()->count() - 1);
        return number_format($average, 1);
    }

    public function getCompetitionFavoritesAttribute()
    {
        $players = array();
        foreach ($this->pi_questions()->where('active', '=', 1)->orderBy('chance_to_win', 'desc')->get() as $player) {
            $player_name = substr($player->question_ticker, 0, strpos($player->question_ticker, '.'));
            $position = $player->cache_current_position_is_yes ? 'Y' : 'N';
            $position = ' (' . number_format($player->cache_current_shares) . $position . ')';
            $players[] = $player_name . ' ' . $player->chance_to_win . $position;
        }
        return implode(' / ', $players);
    }

    public function getInterestLevelAttribute()
    {
        $auto = $this->auto_trade_this_contest ? '(AutoTrade ' . $this->pi_autotrade_speed . ') ' : '';
        $questions = $this->pi_questions()->where('active', '=', 1);
        $price_total = number_format($questions->sum('cache_market_support_yes_side_price'));
        $price_spread = number_format($questions->avg('cache_market_support_net_price_spread'));
        $shares_today = number_format($questions->sum('cache_todays_volume'));
        $shares_total = number_format($questions->sum('cache_total_shares'));
        return $auto . $price_total . '¢ total / ' . $price_spread . '¢ avg spread. ' . $shares_today . ' shares today / ' . $shares_total . ' total';
    }

    public function saveContest($input)
    {
        $this->name = $input['name'];
        $this->category = $input['category'];
        $this->url_of_answer = $input['url_of_answer'];
        $this->rcp_scrape_frequency = $input['rcp_scrape_frequency'];
        $this->rcp_scrapes_per_minute = $input['rcp_scrapes_per_minute'];
        $this->rcp_update_txt_alert = isset($input['rcp_update_txt_alert']) ? 1 : 0;
        $this->save();
    }

    public function saveContestMini($input)
    {
        if ($input['category'] == 'fundraising') {
            $this->name = $input['committee'] . ' ' . $input['month'] . ' Line ' . $input['details'];
            $this->fundraising_committee = $input['committee'];
            $this->fundraising_month = $input['month'];
            $this->fundraising_description = $input['details'];
        } else {
            $this->name = $input['name'];
        }

        $this->category = $input['category'];
        $this->url_of_answer = $input['url_of_answer'];
        $this->save();
    }

    public function saveContestTradingForm($input)
    {
        $this->auto_trade_this_contest = isset($input['auto_trade_this_contest']) ? 1 : 0;
        $other_fields = [
            'max_shares_to_hold',
            'shares_per_trade',
            'shares_in_blocking_bid',
            'implied_bias',
            'implied_variance',
        ];
        foreach ($other_fields as $field) {
            if (isset($input[$field])) {
                $this->$field = $input[$field];
            }
        }
        $this->save();
    }

    public function activate()
    {
        $this->active = 1;
        $this->save();
    }
    
    public function deactivate()
    {
        $this->active = 0;
        $this->save();
        foreach ($this->pi_questions as $q) {
            $q->deactivate();
        }
    }

    public function pi_questions()
    {
        return $this->hasMany(PiQuestion::class);
    }

    public function pi_markets()
    {
        return $this->hasMany(PiMarket::class);
    }

    public function rcp_scrapes()
    {
        return $this->hasMany(RcpScrape::class);
    }

    public function rcp_scrape_updates()
    {
        return $this->hasMany(RcpScrape::class)
            ->where('has_change_since_last_scrape', '=', 1)
            ->orderBy('created_at', 'desc')
            ->take(7)
            ->get()
        ;
    }

    public function rcp_updates()
    {
        return $this->hasMany(RcpUpdate::class);
    }

    public function rcp_drop_trades()
    {
        return $this->hasMany(RcpDropTrade::class);
    }

    public function rcp_add_trades()
    {
        return $this->hasMany(RcpAddTrade::class);
    }

    public function active_add_trades($pollster)
    {
        return $this->rcp_add_trades()
                    ->where('active', '=', 1)
                    ->where('rcp_contest_pollster_id', '=', $pollster->id)
                    ->where('poll_result', '=', $pollster->un_included_actual_result)
                    ->get()
                    ->sortByDesc('shares');
    }

    public function last_rcp_update()
    {
        return $this->rcp_updates()->orderBy('rcp_timestamp', 'desc')->first();
    }

    public function rcp_contest_pollsters()
    {
        return $this->hasMany(RcpContestPollster::class);
    }

    public function rcp_contest_polls()
    {
        return $this->hasManyThrough(RcpContestPoll::class, RcpContestPollster::class);
    }

    public function scrapes()
    {
        return $this->hasManyThrough(Scrape::class, PiMarket::class);
    }

    public function lastTenPolls()
    {
        return $this->rcp_contest_polls()->orderBy('date_end', 'desc')->get()->take(10);
    }

    /**
     * First check if the latest update had drops.
     * Then check if there are RcpDropTrades for this PiContest.
     * Then check if the actual drops match those specified in the RcpDropTrade - EXCLUDING Gallup/Ras for Obama.
     */
    public function checkForDropTrades()
    {
        if ($this->last_rcp_update()->rcp_update_drops->count() && $this->rcp_drop_trades->count()) {

            // Exclude Gallup and Ras Obama drops, if applicable. These are truly only ever replaced.
            $exclusions = array(1345, 1349,);

            $actual_drops = $this->last_rcp_update()->rcp_update_drops->pluck('rcp_contest_pollster_id')->toArray();
            if (count($actual_drops) > 1) {
                foreach ($exclusions as $element) {
                    if (in_array($element, $actual_drops)) {
                        $loc = array_search($element, $actual_drops);
                        unset($actual_drops[$loc]);
                    }
                }
            }
            sort($actual_drops);

            foreach ($this->rcp_drop_trades as $dt) {
                $predicted_drops = array($dt->rcp_contest_pollster_id_1);
                if ($dt->rcp_contest_pollster_id_2) {
                    $predicted_drops[] = $dt->rcp_contest_pollster_id_2;
                    if ($dt->rcp_contest_pollster_id_3) {
                        $predicted_drops[] = $dt->rcp_contest_pollster_id_3;
                        if ($dt->rcp_contest_pollster_id_4) {
                            $predicted_drops[] = $dt->rcp_contest_pollster_id_4;
                        }
                    }
                }
                sort($predicted_drops);

                if ($predicted_drops == $actual_drops) {
                    $bot = new TraderBot();
                    $bot->executeRcpDropTrade($dt);
                    
                    $dt->rcp_update_id = $this->last_rcp_update()->id;
                    $dt->auto_trade_me = 0;
                    $dt->save();
                }
            }
        }
        return;
    }

    /**
     * This is for the auto-trading version of the Rasmussen scraper.
     * We have a polling result, now find the RcpAddTrades that match.
     */
    public function checkRasmussenAddTrades(RcpContestPollster $pollster)
    {
        // d('hi');
        foreach ($this->active_add_trades($pollster) as $trade) {
            // d($trade->shares);
            $bot = new TraderBot();
            $bot->executeRcpAddTrade($trade);
            
            // $trade->rcp_update_id = $this->last_rcp_update()->id;
            $trade->auto_trade_me = 0;
            $trade->save();
        }
        return;
    }
}

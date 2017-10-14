<?php

use Illuminate\Http\Request;

class MController extends Controller {

    /**
     * Loads up my kick-ass dashboard.
     */
    public function getDashboard()
    {
        return View::make('m.dashboard');
    }

    /**
     * Load the last 50 Gallup Obama daily polls in the analytics view.
     */
    public function getGallupDailies()
    {
      $gallup = RcpContestPollster::find(1345);
      $dailies = $gallup->rcp_contest_polls()->orderBy('date_end', 'desc')->take(30)->get();
      return View::make('m.analytics.gallup')
                  ->withDailies($dailies);
    }

    public function postGallupDailies(Request $request)
    {
      $gallup_daily = RcpContestPoll::find($request->poll_id);
      $gallup_daily->gallup_daily_confirmed = $request->gallup_daily_confirmed;
      $gallup_daily->gallup_daily_estimate = $request->gallup_daily_estimate;
      $gallup_daily->save();
      return redirect()->back()
          ->with('success', 'Daily data was saved.');
    }

    /**
     * Load the last 50 Rasmussen Obama daily polls in the analytics view.
     */
    public function getRasmussenDailies()
    {
      $rasmussen = RcpContestPollster::find(1349);
      $dailies = $rasmussen->rcp_contest_polls()->orderBy('date_end', 'desc')->take(30)->get();
      return View::make('m.analytics.rasmussen')
                  ->withDailies($dailies);
    }

    public function postRasmussenDailies(Request $request)
    {
      $rasmussen_daily = RcpContestPoll::find($request->poll_id);
      $rasmussen_daily->rasmussen_daily_estimate = $request->rasmussen_daily_estimate;
      $rasmussen_daily->save();
      return redirect()->back()
          ->with('success', 'Daily data was saved.');
    }

    /**
     * Do calculations and put stuff into an array. Much too complex of business logic to have in the View.
     */
    public function getMoFlow($pi_contest_id)
    {
        $contest = PiContest::find($pi_contest_id);
        $questions = $contest->pi_questions;
        $all_markets = array();
        foreach ($questions as $question) {
          $q_markets = array();
          $q_markets[] = $question->pi_markets()
                          ->where('is_from_coordinated_scrape', '=', 1)
                          ->orderBy('id', 'desc')
                          ->take(75)
                          ->get()
                          ->toArray()
                        ;

          $q_markets = current($q_markets);
          for ($i=0; $i < (count($q_markets)-1); $i++) { 
            $q_markets[$i]['change_price'] = $q_markets[$i]['last_price'] - $q_markets[$i + 1]['last_price'];
            $q_markets[$i]['change_todays_volume'] = $q_markets[$i]['todays_volume'] - $q_markets[$i + 1]['todays_volume'];
            $q_markets[$i]['new_shares'] = $q_markets[$i]['total_shares'] - $q_markets[$i + 1]['total_shares'];
            $q_markets[$i]['swapped_shares'] = $q_markets[$i]['change_todays_volume'] - abs($q_markets[$i]['new_shares']);
            $q_markets[$i]['change_yes_price'] = $q_markets[$i]['market_support_yes_side_price'] - $q_markets[$i + 1]['market_support_yes_side_price'];
            $q_markets[$i]['change_no_price'] = $q_markets[$i]['market_support_no_side_price'] - $q_markets[$i + 1]['market_support_no_side_price'];
            $q_markets[$i]['change_yes_dollars'] = $q_markets[$i]['market_support_yes_side_dollars'] - $q_markets[$i + 1]['market_support_yes_side_dollars'];
            $q_markets[$i]['change_no_dollars'] = $q_markets[$i]['market_support_no_side_dollars'] - $q_markets[$i + 1]['market_support_no_side_dollars'];
            $q_markets[$i]['change_net_price_spread'] = $q_markets[$i]['market_support_net_price_spread'] - $q_markets[$i + 1]['market_support_net_price_spread'];
            $q_markets[$i]['change_net_dollars'] = $q_markets[$i]['market_support_net_dollars'] - $q_markets[$i + 1]['market_support_net_dollars'];
          }
          $all_markets[$question->question_ticker] = $q_markets;
        }

        $scrapes = PiMarket::where('pi_contest_id', '=', $pi_contest_id)
                    ->where('is_from_coordinated_scrape', '=', 1)
                    ->orderBy('id', 'desc')
                    ->take(60)
                    ->get()
                    ->pluck('scrape_id')
                    ->toArray()
                  ;
        $scrapes = array_unique($scrapes);

        $markets = array();
        foreach ($scrapes as $scrape_id) {
          $scrape = Scrape::find($scrape_id);
          if ($scrape->pi_markets()->count() == 5 || $contest->category != 'obama') {
            $markets[$scrape_id]['scrape'] = $scrape;
      
            foreach ($all_markets as $q_id => $q_markets) {
              $scrape_market = array_filter($q_markets, function ($var) use ($scrape_id) { 
                return ($var['scrape_id'] == $scrape_id);
              });
              $markets[$scrape_id]['markets'][$q_id] = current($scrape_market);
            }
          }
        }
        return View::make('josh.d3.moflow')
                  ->withMarkets($markets)
        ;
    }

    public function getLines()
    {
      $contests = PiContest::where('active', '=', 1)
                    ->whereIn('category', ['obama', 'debates', 'states_dem', 'states_gop', 'fundraising',])
                    ->orderBy('auto_trade_this_contest', 'desc')
                    ->orderBy('category', 'asc')
                    ->orderBy('name', 'asc')
                    ->get()
                  ;
      $binary_questions = PiQuestion::where('active', '=', 1)
                    ->where('pi_contest_id', '=', 163)
                    ->orderBy('auto_trade_me', 'desc')
                    ->orderBy('question_ticker', 'asc')
                    ->get()
                  ;

      return View::make('josh.d3.lines')
                ->withContests($contests)
                ->withBinaryQuestions($binary_questions)
      ;
    }

    /**
     * Return a json-encoded dataset of PiOffers to an ajax request.
     */
    public function getAjaxMoFlow($scrape_id)
    {
        // Log::info($scrape_id);
        $i=1;
        $data = array();
        $markets = PiMarket::where('scrape_id', '=', $scrape_id)->get();
        foreach ($markets as $market) {
          // if ($market->pi_question->pi_contest_id == 1) {
            $marray = array('name' => $i);
            foreach ($market->pi_offers as $offer) {
              $oarray = array(
                'action' => $offer->action,
                'price' => $offer->price,
                'shares' => $offer->shares,
              );
              $marray['children'][] = $oarray;
            }

            $last_array = array(
              'action' => 'lastPrice',
              'price' => $market->last_price,
              'shares' => 500,
            );
            $marray['children'][] = $last_array;

            $data['children'][] = $marray;
            $i++;
          // }
        }
        return json_encode($data);
    }

    /**
     * Return a json-encoded dataset of PiOffers to an ajax request.
     */
    public function getAjaxLines($pi_contest_id, $pi_question_id, $line_index, $take = 50, $skip = 0)
    {
        $binary = false;
        if ($pi_question_id != 'none') {
          $binary = true;
          $pi_contest_id = 163;
          $contest = PiContest::find($pi_contest_id);
          $questions = $pi_question_id != 'all' ? PiQuestion::where('id', '=', $pi_question_id)->get() : $contest->pi_questions;
        }
        else {
          $contest = PiContest::find($pi_contest_id);
          $questions = $contest->pi_questions;
        }
        $all_markets = array();
        foreach ($questions as $question) {
          if ($question->active) {
            $q_markets = array();
            $q_markets[] = $question->pi_markets()
                            ->where('is_from_coordinated_scrape', '=', 1)
                            ->orderBy('id', 'desc')
                            ->take($take)
                            ->get()
                            ->toArray()
                          ;

            $q_markets = current($q_markets);
            for ($i=0; $i < (count($q_markets)-1); $i++) { 
              $q_markets[$i]['change_price'] = $q_markets[$i]['last_price'] - $q_markets[$i + 1]['last_price'];
              $q_markets[$i]['change_todays_volume'] = max($q_markets[$i]['todays_volume'] - $q_markets[$i + 1]['todays_volume'], 0);
              $q_markets[$i]['new_shares'] = max($q_markets[$i]['total_shares'] - $q_markets[$i + 1]['total_shares'], 0);
              $q_markets[$i]['swapped_shares'] = max($q_markets[$i]['change_todays_volume'] - abs($q_markets[$i]['new_shares']), 0);
              $q_markets[$i]['change_yes_price'] = $q_markets[$i]['market_support_yes_side_price'] - $q_markets[$i + 1]['market_support_yes_side_price'];
              $q_markets[$i]['change_no_price'] = $q_markets[$i]['market_support_no_side_price'] - $q_markets[$i + 1]['market_support_no_side_price'];
              $q_markets[$i]['change_yes_dollars'] = $q_markets[$i]['market_support_yes_side_dollars'] - $q_markets[$i + 1]['market_support_yes_side_dollars'];
              $q_markets[$i]['change_no_dollars'] = $q_markets[$i]['market_support_no_side_dollars'] - $q_markets[$i + 1]['market_support_no_side_dollars'];
              $q_markets[$i]['change_net_price_spread'] = $q_markets[$i]['market_support_net_price_spread'] - $q_markets[$i + 1]['market_support_net_price_spread'];
              $q_markets[$i]['change_net_dollars'] = $q_markets[$i]['market_support_net_dollars'] - $q_markets[$i + 1]['market_support_net_dollars'];
              $q_markets[$i]['ratio_of_ratios'] = max(min($q_markets[$i]['market_support_ratio_dollars'] - $q_markets[$i + 1]['market_support_ratio_price'], 15), -15);
            }
            $all_markets[$question->question_ticker] = $q_markets;
          }
        }

        $scrapes = PiMarket::where('pi_contest_id', '=', $pi_contest_id)
                    ->where('is_from_coordinated_scrape', '=', 1)
                    ->orderBy('id', 'desc')
                    ->take($take)
                    ->get()
                    ->pluck('scrape_id')
                    ->toArray()
                  ;
        $scrapes = array_unique($scrapes);

        $markets = array();
        foreach ($scrapes as $scrape_id) {
          $scrape = Scrape::find($scrape_id);
          if ($binary) {
            $these_markets = array();
            $these_markets['date'] = $scrape->created_at->toDateTimeString();
      
            foreach ($all_markets as $q_id => $q_markets) {
              $scrape_market = array_filter($q_markets, function ($var) use ($scrape_id) { 
                return ($var['scrape_id'] == $scrape_id);
              });
              if ($scrape_market) {
                $this_market = current($scrape_market);
                $these_markets[$q_id] = isset($this_market[$line_index]) ? $this_market[$line_index] : 0;
              }
            }
            if (count($these_markets) > 1) {
              $markets[] = $these_markets;
            }
          }
          elseif ($contest->category != 'obama' || $scrape->pi_markets()->count() == 5) {
            $these_markets = array();
            $these_markets['date'] = $scrape->created_at->toDateTimeString();
      
            foreach ($all_markets as $q_id => $q_markets) {
              $scrape_market = array_filter($q_markets, function ($var) use ($scrape_id) { 
                return ($var['scrape_id'] == $scrape_id);
              });
              $this_market = current($scrape_market);
              $these_markets[$q_id] = isset($this_market[$line_index]) ? $this_market[$line_index] : 0;
            }
            $markets[] = $these_markets;
          }
        }

        return json_encode($markets);
    }

    public function getAnalysisDashboard()
    {
      $contests = PiContest::where('active', '=', 1)
                    ->whereIn('category', ['obama', 'debates', 'states_dem', 'states_gop', 'fundraising',])
                    ->orderBy('auto_trade_this_contest', 'desc')
                    ->orderBy('category', 'asc')
                    ->orderBy('name', 'asc')
                    ->get()
                  ;
      return View::make('josh.d3.analysis')
                ->withContests($contests)
      ;
    }

}
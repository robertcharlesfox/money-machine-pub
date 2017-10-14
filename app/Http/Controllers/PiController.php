<?php

use Illuminate\Http\Request;

use PredictIt\CompetitionTrader;
use App\Jobs\ScrapePiQuestion;

class PiController extends Controller {

    public $trade_frequencies = array(15, 30, 60, 120, 360);
    public $trade_categories = array('default', 'Yes', 'No',);

    /**
     * Find and create related PiQuestions if none exist, then go to an admin interface for this PiContest/PiQuestions.
     */
    public function getCompetitionQuestions($contest_id)
    {
      $contest = PiContest::find($contest_id);
      if ( ! $contest->pi_questions->count()) {
        $operator = new BotPiOperatorNonAuth();
        $operator->findCompetitionQuestions($contest);
      }
      return view('m.rcp.competition.projections')
          ->withContest($contest)
          ->withTradeCategories($this->trade_categories)
      ;
    }

    /**
     * Save values for a PredictIt competition question.
     *
     * @param  Request  $request
     * @return Response
     */
    public function postCompetitionQuestionForm(Request $request)
    {
        $this->validate($request, [
            'chance_to_win' => 'required',
        ]);

        $question = PiQuestion::find($request->input('question_id'));
        $question->saveCompetitionQuestion($request->input());
        return redirect()->back()
            ->with('success', 'Competition Question saved.')
        ;
    }

    /**
     * Visit flagged PiQuestion pages for an entire competition.
     */
    public function visitCompetitionQuestions($competition_id, $source = '')
    {
        $competition = PiContest::find($competition_id);
        if ($competition) {
          $scrape = new Scrape();
          $scrape->save();
          foreach ($competition->getCurrentPiQuestions() as $question) {
              if ($question->active && $question->auto_trade_me) {
                $job = (new ScrapePiQuestion($question, $scrape->id))->onQueue('pi_scrapes');
                $this->dispatch($job);
              }
          }
        }
        if ($source != 'bot') {
          return redirect()->back()
              ->with('success', 'Dispatched Competition Visit jobs for ' . $competition->name)
          ;
        }
    }

    private function parsePiQuestionPiId($market_url)
    {
      $market_id = substr($market_url, strpos($market_url, '/', strpos($market_url, 'Contract')) + 1);
      $market_id = substr($market_id, 0, strpos($market_id, '/'));
      // $button_id = $buy_or_sell == 'buy' ? 'simple' . $yes_or_no : 'sell' . $yes_or_no . '-' . $market_id;
      return $market_id;
    }
    /**
     * Trade flagged PiQuestions. Third parameter in autoVisitCompetition indicates trading is ON.
     */
    public function tradeCompetitionQuestions($competition_id, $source = '')
    {
        $rcp_contest = PiContest::find($competition_id);
        $competition = $rcp_contest->getLatestContest();
        if ($competition) {
          $new_values = [];
          $contestants = $rcp_contest->getCurrentPiQuestions();
          $total_chance = 0;
          foreach ($contestants as $c) {
            $c_pi_id = $this->parsePiQuestionPiId($c->url_of_market);
            $new_values[$c_pi_id] = $c->chance_to_win;
            $total_chance += $c->chance_to_win;
          }

          if (!($total_chance > 95 && $total_chance < 105)) {
            die('this competition does not add up to 100.');
          }

          $max_risk = 1;
          $urgency = 3;
          $ct = new CompetitionTrader();
          $ct->runCompetition($competition, $new_values, $max_risk, $urgency);
        }
    }

    /**
     * Create auto-trade orders for this contest.
     */
    public function autotradeQuestions($contest_type)
    {
        $active_contests = PiContest::where('active', '=', 1)
                              ->where('category', '=', $contest_type)
                              ->where('auto_trade_this_contest', '=', 1)
                              ->get()
                            ;
        
        if ($active_contests->count() > 0) {
          foreach ($active_contests as $active_contest) {
            $bot = new ScraperBot();
            $bot->autoVisitCompetition($active_contest->id, 0, true);
          }
        }
        else {
          Log::info($contest_type . ' contest not ready for AutoTrade');
        }
    }

    /**
     * Cancel all open auto-trade orders for this Obama Approval contest.
     */
    public function cancelObamaOrders()
    {
        $active_contests = PiContest::where('active', '=', 1)->get();
        $obama_contests = $active_contests->filter(function ($contest) {
            return stristr($contest->name, 'Obama Approval');
        });
        if ($obama_contests->count() > 0) {
          $contest = $obama_contests->first();
          $contest->auto_trade_this_contest = 0;
          $contest->save();
          $bot = new ScraperBot();
          $bot->cancelCompetitionQuestionOrders($contest->id);
        }
    }

    /**
     * Cancel all open auto-trade orders for all PiQuestions in this competition.
     */
    public function cancelCompetitionQuestionOrders($competition_id, $source = '')
    {
        $competition = PiContest::find($competition_id);
        if ($competition) {
          $bot = new ScraperBot();
          $bot->cancelCompetitionQuestionOrders($competition_id);
        }
        if ($source != 'bot') {
          return redirect()->back()
              ->with('success', 'Dispatched Cancel Competition Order jobs for ' . $competition->name)
          ;
        }
    }

    /**
     * Visit a PiQuestion page on predictit.org.
     */
    public function visitQuestion($question_id, $source = '')
    {
        $question = PiQuestion::find($question_id);
        if ($question) {
          $bot = new ScraperBot();
          $bot->autoVisitQuestion($question_id);
        }
        if ($source != 'bot') {
          return redirect()->back()
              ->with('success', 'Dispatched Visit job for ' . $question->question_ticker)
          ;
        }
    }

    /**
     * Trade a PiQuestion page on predictit.org. Flagged by second parameter in autoVisitQuestion.
     */
    public function tradeQuestion($question_id, $source = '')
    {
        $question = PiQuestion::find($question_id);
        if ($question) {
          $bot = new ScraperBot();
          $bot->autoVisitQuestion($question_id, true);
        }
        if ($source != 'bot') {
          return redirect()->back()
              ->with('success', 'Dispatched Trade job for ' . $question->question_ticker)
          ;
        }
    }

    /**
     * Cancel all open auto-trade orders for this PiQuestion.
     */
    public function cancelQuestionOrders($question_id, $source = '')
    {
        $question = PiQuestion::find($question_id);
        if ($question) {
          $bot = new ScraperBot();
          $bot->cancelQuestionOrders($question_id);
        }
        if ($source != 'bot') {
          return redirect()->back()
              ->with('success', 'Dispatched Cancel Orders job for ' . $question->question_ticker)
          ;
        }
    }

    public function scrapeSingleContestFromAdmin($contest_id)
    {
      $scrape = new Scrape();
      $scrape->save();

      $operator = new BotPiOperatorNonAuth();
      foreach (PiContest::find($contest_id)->pi_questions as $question) {
          if ($question->active && $question->auto_trade_me) {
            $operator->visitQuestionNonAuth($question, $scrape->id);
            sleep(1);
          }
      }
    }

    /**
     * Scrapes market data from predictit.org
     */
    public function scrapeMarkets($contest_type, $speed)
    {
      // Find contests with a matching category
      $scrape = new Scrape();
      $scrape->save();

      if ($contest_type != 'binary') {
        $active_contests = PiContest::where('active', '=', 1)
                              ->where('category', '=', $contest_type)
                              ->where('auto_trade_this_contest', '=', 1)
                              ->where('pi_autotrade_speed', '=', $speed)
                              ->get()
                            ;
        
        foreach ($active_contests as $contest) {
            foreach ($contest->pi_questions as $question) {
                if ($question->active && $question->auto_trade_me) {
                  $job = (new ScrapePiQuestion($question, $scrape->id))->onQueue('pi_scrapes');
                  $this->dispatch($job);
                }
            }
        }
      }
      else {
          $contest = PiContest::find(163);
          foreach ($contest->pi_questions()->where('pi_autotrade_speed', '=', $speed)->get() as $question) {
              if ($question->active && $question->auto_trade_me) {
                $job = (new ScrapePiQuestion($question, $scrape->id))->onQueue('pi_scrapes');
                $this->dispatch($job);
              }
          }
      }
    }

    public function analyzeMarketDepth()
    {
      setlocale(LC_MONETARY, 'en_US');
      $values = array();
      $recent_scrapes = Scrape::orderBy('id', 'desc')->take(5)->get();
      foreach ($recent_scrapes as $scrape) {
        $markets = PiMarket::where('scrape_id', '=', $scrape->id)->get();
        foreach ($markets as $market) {
          $buy_yes_offers = $market->pi_offers()->where('action', '=', 'sellYes')->get();
          $yes_value = 0;
          foreach ($buy_yes_offers as $yes) {
            $yes_value += ($yes->price * $yes->shares);
          }
          $buy_no_offers = $market->pi_offers()->where('action', '=', 'buyYes')->get();
          $no_value = 0;
          foreach ($buy_no_offers as $no) {
            $no_value += ((100 - $no->price) * $no->shares);
          }
          $values[$market->pi_question->question_ticker][$market->id]['yesValue'] = money_format('%#7.0n', $yes_value / 100);
          $values[$market->pi_question->question_ticker][$market->id]['noValue'] = money_format('%#7.0n', $no_value / 100);
          $values[$market->pi_question->question_ticker][$market->id]['netYes'] = money_format('%#7.0n', ($yes_value - $no_value) / 100);
          $values[$market->pi_question->question_ticker][$market->id]['lastPrice'] = money_format('%#3n', $market->last_price / 100);
          $values[$market->pi_question->question_ticker][$market->id]['todaysVolume'] = $market->todays_volume;
        }
      }
      d($values);
      die();
    }
}
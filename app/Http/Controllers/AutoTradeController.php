<?php

use Illuminate\Http\Request;

use App\Jobs\TraderBotExecuteAutoTrade;

class AutoTradeController extends Controller {

    private $fundraising_committees = [
        'C00575795 - (Clinton)',
        'C00580100 - (Trump)',
        'C00605568 - (Johnson)',
        'C00581199 - (Stein)',
        'C00010603 - (DNC)',
        'C00003418 - (RNC)',
    ];

    public function getDashboard()
    {
      return View::make('josh.dashboard')
        ->withFundraisingCommittees($this->fundraising_committees)
      ;
    }

    public function getTvC2way()
    {
      return View::make('josh.states')
          ->withPiContests(
            PiContest::where('category', '=', 'polls_clinton_vs_trump')
              ->where('active', '=', 1)
              ->orderBy('auto_trade_this_contest', 'desc')
              ->orderBy('name', 'asc')
              ->get()
            )
          ->withPageTitle('TvC 2-way')
      ;
    }

    public function getFundraising()
    {
      return View::make('josh.states')
          ->withPiContests(
            PiContest::where('category', '=', 'fundraising')
              ->where('active', '=', 1)
              ->orderBy('auto_trade_this_contest', 'desc')
              ->orderBy('name', 'asc')
              ->get()
            )
          ->withPageTitle('Fundraising')
      ;
    }

    public function getOtherCompetitions($type)
    {
      return View::make('josh.states')
          ->withPiContests(
            PiContest::where('category', '=', $type)
              ->where('active', '=', 1)
              ->orderBy('auto_trade_this_contest', 'desc')
              ->orderBy('name', 'asc')
              ->get()
            )
          ->withPageTitle('Competitions')
      ;
    }

    public function getStatesDem()
    {
      return View::make('josh.states')
          ->withPiContests(
            PiContest::where('category', '=', 'states_dem')
              ->where('active', '=', 1)
              ->orderBy('auto_trade_this_contest', 'desc')
              ->orderBy('name', 'asc')
              ->get()
            )
          ->withPageTitle('Dem States')
      ;
    }

    public function getStatesGop()
    {
      return View::make('josh.states')
          ->withPiContests(
            PiContest::where('category', '=', 'states_gop')
              ->where('active', '=', 1)
              ->orderBy('auto_trade_this_contest', 'desc')
              ->orderBy('name', 'asc')
              ->get()
            )
          ->withPageTitle('GOP States')
      ;
    }

    public function getDebates()
    {
      return View::make('josh.states')
          ->withPiContests(
            PiContest::where('category', '=', 'debates')
              ->where('active', '=', 1)
              ->orderBy('auto_trade_this_contest', 'desc')
              ->orderBy('name', 'asc')
              ->get()
            )
          ->withPageTitle('Debate')
      ;
    }

    public function getObama()
    {
      return View::make('josh.states')
          ->withPiContests(
            PiContest::where('category', '=', 'obama')
              ->where('active', '=', 1)
              ->orderBy('auto_trade_this_contest', 'desc')
              ->orderBy('name', 'asc')
              ->get()
            )
          ->withPageTitle('Obama Approval')
      ;
    }

    public function getBinary()
    {
      return View::make('josh.binaries')
          ->withPiQuestions(
            PiQuestion::where('pi_contest_id', '=', 163)
            ->where('active', '=', 1)
            ->orderBy('auto_trade_me', 'desc')
            ->orderBy('question_ticker', 'asc')
            ->get()
          )
          ->withPageTitle('Binary Markets')
      ;
    }

    public function getCheckTrades()
    {
      $bot = new TraderBot();
      $bot->checkTrades();
      // return redirect()->back();
    }

    public function getChangeBinarySpeed($question_id, $speed)
    {
        $q = PiQuestion::find($question_id);
        $q->pi_autotrade_speed = $speed;
        $q->save();
        return redirect()->back()
            ->with('success', 'Question AutoTrade Speed was updated.')
        ;
    }

    public function getChangeContestSpeed($contest_id, $speed)
    {
        $c = PiContest::find($contest_id);
        $c->pi_autotrade_speed = $speed;
        $c->save();
        return redirect()->back()
            ->with('success', 'Contest AutoTrade Speed was updated.')
        ;
    }

    public function getActivateContest($contest_id)
    {
        $c = PiContest::find($contest_id);
        $c->auto_trade_this_contest = 1;
        $c->save();
        return redirect()->back()
            ->with('success', 'Contest AutoTrade was activated.')
        ;
    }

    public function getDeactivateContest($contest_id)
    {
        $c = PiContest::find($contest_id);
        $c->auto_trade_this_contest = 0;
        $c->save();
        return redirect()->back()
            ->with('success', 'Contest AutoTrade was deactivated.')
        ;
    }

    /**
     * Store or update a PredictIt contest.
     *
     * @param  Request  $request
     * @return Redirect
     */
    public function postContest(Request $request)
    {
        if ($request->category == 'fundraising') {
          $this->validate($request, [
              'committee' => 'required',
              'month' => 'required',
              'details' => 'required',
              'category' => 'required',
              'url_of_answer' => 'required',
          ]);
        } else {
          $this->validate($request, [
              'name' => 'required',
              'category' => 'required',
              'url_of_answer' => 'required',
          ]);
        }

        if ($request->category == 'binary') {
          $question = new PiQuestion();
          $question->saveQuestionMini($request->input());
          return redirect()->back()
              ->with('success', 'Question was saved.')
          ;
        }
        else {
          $contest = new PiContest();
          $contest->saveContestMini($request->input());
          return redirect()->back()
              ->with('success', 'Contest was saved.')
          ;
        }
    }

    public function getTradeQueue()
    {
      $competitions = PiContest::where('auto_trade_this_contest', '=', 1)->where('active', '=', 1)->get();
      foreach ($competitions as $competition) {
        foreach ($competition->pi_questions as $trade) {
          if ($trade->active && $trade->auto_trade_me) {
            // $job = (new TraderBotExecuteAutoTrade($trade))->onQueue('autotrades');
            // $this->dispatch($job);
          }
        }
      }
    }
}
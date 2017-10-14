<?php

use Illuminate\Http\Request;

class OneRingController extends Controller {

    public $scrape_frequencies = array(1, 2, 5, 10, 15, 20, 30, 60,);
    public $tradeable_contest_categories = [
      'obama', 'doc', 'congress', 'polls_clinton_vs_trump',
      'polls_clinton_vs_trump_pa', 'polls_clinton_vs_trump_fl', 
      'polls_clinton_vs_trump_oh', 'polls_clinton_vs_trump_nc', 'polls_clinton_vs_trump_nv', 
      'Johnson', 'Stein', 'clinton_fav', 'trump_fav',
      'fundraising',
    ];

    public function getDestiny()
    {
        $o = new OneRing();
                // $o->releaseNazgulTrade(28);
        // Dispatch The Nine.
        $o->dispatchNazgul();

        // Dispatch The Seven.
        $o->dispatchRcpScrapers();
        // $o->dispatchPollsterScrapers();
        // $o->dispatchFundraisingScrapers();
        // $o->dispatchWarmups();
        // $o->checkStatus();
    }

    /**
     * D3 bubbles visualization, in progress.
     */
    public function makeBubbles()
    {
      $bubble_contests = array();
      $bubble_questions = array();
      foreach ($this->tradeable_contest_categories as $category) {
        $contest = PiContest::where('category', '=', $category)
          ->where('active', '=', 1)
          ->orderBy('id', 'desc')
          ->first();
        if ($contest) {
          $these_questions = $contest->pi_questions()->where('active', '=', 1)->get()->toArray();
          $bubble_contests[] = $contest;
          $bubble_questions = array_merge($bubble_questions, $these_questions);
        }
      }
      return View::make('m.d3.bubbles')
          ->withBubbleContests($bubble_contests)
          ->withBubbleQuestions($bubble_questions)
      ;
    }

    public function setNazgulStatus($nazgul_id, $status_code)
    {
      $nazgul = Nazgul::find($nazgul_id);
      switch ($status_code) {
        case 'activate':
          $nazgul->active = 1;
          $nazgul->save();
          Cache::forget('status-nazgul-' . $nazgul->id);
          break;
        case 'deactivate':
          $nazgul->active = 0;
          $nazgul->save();
          break;
      }
      return redirect()->back();
    }

    public function getNazgul()
    {
      $nazgul_contests = array();
      $nazgul_questions = array();
      foreach ($this->tradeable_contest_categories as $category) {
        $contest = PiContest::where('category', '=', $category)
          ->where('active', '=', 1)
          ->orderBy('id', 'desc')
          ->first();
        if ($contest) {
          $these_questions = $contest->pi_questions()->where('active', '=', 1)->get()->toArray();
          $nazgul_contests[] = $contest;
          $nazgul_questions = array_merge($nazgul_questions, $these_questions);
        }
      }
      return View::make('josh.onering.nazgul')
          ->withNazguls(Nazgul::where('active', '=', 1)->orderBy('id', 'desc')->get())
          ->withNazgulContests($nazgul_contests)
          ->withNazgulQuestions($nazgul_questions)
          ->withInactiveNazguls(Nazgul::where('active', '<>', 1)
              ->orderBy('updated_at', 'desc')->take(7)->get())
      ;
    }

    public function postNazgul(Request $request)
    {
      if ($request->input('nazgul_id')) {
        $nazgul = Nazgul::find($request->input('nazgul_id'));
        $nazgul->saveDetails($request->input());
      } elseif ($request->input('pi_question_id')) {
        $nazgul = new Nazgul();
        $nazgul->pi_contest_id = $request->input('pi_contest_id');
        $nazgul->pi_question_id = $request->input('pi_question_id');
        $nazgul->active = 1;
        $nazgul->save();
      }
      return redirect()->back();
    }

    public function getRcpScrapers()
    {
        $candidate_contests = PiContest::where('category', '=', 'poll_other')
            ->where('active', '=', 1)
            ->orderBy('rcp_scrape_frequency', 'asc')
            ->orderBy('rcp_scrapes_per_minute', 'asc')
            ->get();

        $approval_contests = PiContest::where('category', '=', 'poll_rcp')
            ->where('active', '=', 1)
            ->orderBy('rcp_scrape_frequency', 'asc')
            ->orderBy('rcp_scrapes_per_minute', 'asc')
            ->get();

        return view('josh.onering.rcp_scrapers')
            ->withApprovalContests($approval_contests)
            ->withCandidateContests($candidate_contests)
            ->withScrapeFrequencies($this->scrape_frequencies)
        ;
    }

    public function postRcpScrapers(Request $request)
    {
      $contest = PiContest::find($request->input('contest_id'));
      $contest->rcp_scrape_frequency = $request->input('rcp_scrape_frequency');
      $contest->rcp_scrapes_per_minute = $request->input('rcp_scrapes_per_minute');
      $contest->save();
      return redirect()->back();
    }

    /**
     * This is the master auto-trade killswitch
     */
    public function activateAutotrade()
    {
      // This is just a dummy record to use for saving data.
      $autotrade = PiContest::find(186);
      $autotrade->auto_trade_this_contest = 1;
      $autotrade->save();
      return redirect()->back();
    }

    /**
     * This is the master auto-trade killswitch
     */
    public function deActivateAutotrade()
    {
      // This is just a dummy record to use for saving data.
      $autotrade = PiContest::find(186);
      $autotrade->auto_trade_this_contest = 0;
      $autotrade->save();
      return redirect()->back();
    }
}
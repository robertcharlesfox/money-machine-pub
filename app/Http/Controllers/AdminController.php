<?php

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public $categories = array(
        'poll_rcp' => 'RCP Average Poll',
        'poll_other' => 'Other Poll',
        'states_dem' => 'Dem Primary State',
        'states_gop' => 'GOP Primary State',
        'binary' => 'Isolated Yes/No Event',
        'competition' => 'Competition with One Winner',
    );

    public $scrape_frequencies = array(0, 1, 2, 5, 15, 30, 60, 120, 360);
    public $poll_frequencies = array('1daily', '2weekly', '3semi-regular', '4irregular');

    public function getMarkPollAsOld($poll_id, $is_old)
    {
        $poll = RcpContestPoll::find($poll_id);
        $poll->mark_as_old = $is_old;
        $poll->save();
        return redirect()->back();
    }

    public function getContests()
    {
        return view('m.admin.contests.index')
            ->withPiContests(PiContest::where('active', '=', 1)->orderBy('category', 'desc')->get())
        ;
    }

    public function getInactiveContests()
    {
        return view('m.admin.contests.index')
            ->withPiContests(PiContest::where('active', '=', 0)->get())
        ;
    }

    public function getActivateContest($contest_id)
    {
        $c = PiContest::find($contest_id);
        $c->activate();
        return redirect('/admin/contests')
            ->with('success', 'Contest was activated.')
        ;
    }

    public function getDeactivateContest($contest_id)
    {
        $c = PiContest::find($contest_id);
        $c->deactivate();
        return redirect('/admin/contests')
            ->with('success', 'Contest was deactivated.')
        ;
    }

    public function reactivateContestPollster($contest_pollster_id)
    {
        $c = RcpContestPollster::find($contest_pollster_id);
        $c->reactivate();
        return redirect()->back()
            ->with('success', 'Pollster scraping was reactivated.')
        ;
    }

    /**
     * Start with just Obama approval pollsters.
     */
    public function getContestPollsters()
    {
        return view('m.admin.contest_pollsters.index')
            ->withContestPollsters(RcpContestPollster::where('pi_contest_id', '=', 1)->orderBy('update_frequency', 'asc')->orderBy('name', 'asc')->get())
            ->withPollFrequencies($this->poll_frequencies)
        ;
    }

    public function getContestForm($contest_id = '')
    {
        if ($contest_id) {
            return view('m.admin.contests.edit')
                ->withCategories($this->categories)
                ->withScrapeFrequencies($this->scrape_frequencies)
                ->withContest(PiContest::find($contest_id))
            ;
        }
        return view('m.admin.contests.edit')
            ->withCategories($this->categories)
            ->withScrapeFrequencies($this->scrape_frequencies)
        ;
    }

    /**
     * Save attributes of a RcpContestPollster.
     *
     * @param  Request  $request
     * @return Redirect
     */
    public function postContestPollsterForm(Request $request)
    {
        $cp = RcpContestPollster::find($request->contest_pollster_id);
        $cp->saveContestPollster($request->input());
        return redirect()->back()
            ->with('success', 'ContestPollster was saved.')
        ;
    }

    /**
     * Save early poll results for a RcpContestPollster.
     *
     * @param  Request  $request
     * @return Redirect
     */
    public function postContestPollsterResultForm(Request $request)
    {
        $cp = RcpContestPollster::find($request->contest_pollster_id);
        $cp->saveContestPollsterResult($request->input());
        return redirect()->back()
            ->with('success', 'ContestPollster Result was saved.')
        ;
    }

    /**
     * Store or update a PredictIt contest.
     *
     * @param  Request  $request
     * @return Redirect
     */
    public function postContestForm(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'category' => 'required',
            'url_of_answer' => 'required',
        ]);

        $contest_id = $request->input('contest_id');
        $contest = $contest_id ? PiContest::find($contest_id) : new PiContest();
        $contest->saveContest($request->input());
        return redirect('/admin/contests')
            ->with('success', 'Contest was saved.')
        ;
    }

    /**
     * Save PredictIt contest auto-trading details.
     *
     * @param  Request  $request
     * @return Redirect
     */
    public function postContestTradingForm(Request $request)
    {
        // $this->validate($request, [
        //     'max_shares_to_hold' => 'required',
        //     'shares_per_trade' => 'required',
        //     'shares_in_blocking_bid' => 'required',
        // ]);

        $contest = PiContest::find($request->input('contest_id'));
        $contest->saveContestTradingForm($request->input());
        return redirect()->back()
            ->with('success', 'Contest was updated.')
        ;
    }

    public function postContestRandomPollsForm(Request $request)
    {
        $contest = PiContest::find($request->input('contest_id'));
        $contest->random_polls_to_add = $request->input('random_polls_to_add');
        $contest->save();
        return redirect()->back()
            ->with('success', 'Contest was updated.')
        ;
    }

    public function getQuestions()
    {
        return view('m.admin.questions.index')
            ->withPiQuestions(PiQuestion::where('active', '=', 1)->get())
        ;
    }

    public function getInactiveQuestions()
    {
        return view('m.admin.questions.index')
            ->withPiQuestions(PiQuestion::where('active', '=', 0)->get())
        ;
    }

    public function getQuestionForm($question_id = '')
    {
        if ($question_id) {
            return view('m.admin.questions.edit')
                ->withContests(PiContest::where('active', '=', 1)->get())
                ->withScrapeFrequencies($this->scrape_frequencies)
                ->withQuestion(PiQuestion::find($question_id))
            ;
        }
        return view('m.admin.questions.edit')
            ->withContests(PiContest::where('active', '=', 1)->get())
            ->withScrapeFrequencies($this->scrape_frequencies)
        ;
    }

    public function getActivateQuestion($question_id)
    {
        $q = PiQuestion::find($question_id);
        $q->activate();
        return redirect('/admin/questions')
            ->with('success', 'Question was activated.')
        ;
    }

    public function getDeactivateQuestion($question_id)
    {
        $q = PiQuestion::find($question_id);
        $q->deactivate();
        return redirect()->back()
        // return redirect('/admin/questions')
            ->with('success', 'Question was deactivated.')
        ;
    }

    /**
     * Store or update a PredictIt question.
     * Scrapes additional details from the market's page first.
     * If scraping fails, default values are used.
     *
     * @param  Request  $request
     * @return Response
     */
    public function postQuestionForm(Request $request)
    {
        $this->validate($request, [
            'pi_contest_id' => 'required',
            'url_of_market' => 'required',
        ]);

        $question_id = $request->input('question_id');
        $question = $question_id ? PiQuestion::find($question_id) : new PiQuestion();
        $question->saveQuestion($request->input());
        return redirect('/admin/questions')
            ->with('success', 'Question was saved.')
        ;
    }
}

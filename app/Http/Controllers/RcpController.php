<?php

class RcpController extends Controller {

    public $poll_frequencies = array('1daily', '2weekly', '3semi-regular', '4irregular');

    /**
     * Scrapes polling data from realclearpolitics.com
     */
    public function getRcpPolls()
    {
        $rcp_contests = PiContest::where('category', '=', 'poll_rcp')->get();
        foreach ($rcp_contests as $contest) {
            $scrape = new RcpScrape();
            $scrape->scrape($contest);
            if ($scrape->has_change_since_last_scrape) {
                return redirect('rcp/scrapes')
                    ->withSuccess($scrape->update_text)
                ;
            }
        }
        return redirect('rcp/scrapes')
            ->withSuccess('No RCP Updates.')
        ;
    }

    public function getRcpScrapes()
    {
        return view('m.scrapes')
            ->withRcpContests(PiContest::where('category', '=', 'poll_rcp')->get())
        ;
    }

    public function getRcpPollsters($pi_contest_id = '')
    {
        return view('m.rcp.obama.pollsters')
            ->withRcpContests(PiContest::where('category', '=', 'poll_rcp')->get())
        ;
    }

    public function getRcpApprovalUpdates($pi_contest_id)
    {
        return view('m.updates')
            ->withContest(PiContest::find($pi_contest_id))
            ->withRcpDays(RcpDay::orderBy('rcp_date', 'desc')->get())
        ;
    }

    public function getRcpDemUpdates()
    {
        return view('m.updates')
            ->withContest(PiContest::find(12))
            ->withRcpDays(RcpDay::orderBy('rcp_date', 'desc')->get())
        ;
    }

    public function getRcpGopUpdates()
    {
        return view('m.updates')
            ->withContest(PiContest::find(13))
            ->withRcpDays(RcpDay::orderBy('rcp_date', 'desc')->get())
        ;
    }

    public function getRcpDemProjections($include_debate = false)
    {
        return $this->getRcpCandidateProjections(PiContest::find(12), new RcpCandidateDemUpdate(), $include_debate);
    }

    public function getRcpGopProjections($include_debate = false)
    {
        return $this->getRcpCandidateProjections(PiContest::find(13), new RcpCandidateGopUpdate(), $include_debate);
    }

    public function getRcpCandidateProjections(PiContest $contest, $candidate_update, $include_debate)
    {
        $rcp_update = $contest->last_rcp_update();
        $recent_polls = $contest->rcp_contest_polls()->orderBy('date_end', 'desc')->get()->take(7);
        $calc = new Calculator();

        $currentStandings = $calc->debateWinners($rcp_update, $candidate_update);
        $randomPollImpact = $calc->randomPollImpact($rcp_update, $candidate_update);
        $projectedStandings = $calc->debateWinnersProjected($rcp_update, $candidate_update);
        
        return view('m.rcp.candidate.projections')
            ->withContest($contest)
            ->withRecentPolls($recent_polls)
            ->withRcpUpdate($rcp_update)
            ->withProjectionPollsters($rcp_update->rcp_contest_pollsters_for_projections())
            ->withDebatePollsters($rcp_update->debate_pollsters())
            ->withOtherPollsters($contest->rcp_contest_pollsters()->orderBy('next_poll_expected', 'asc')->get())
            ->withCandidateUpdate($candidate_update)
            ->withIncludeDebate($include_debate)
            ->withDebateWinners($currentStandings)
            ->withDebateLosers(array_reverse($currentStandings))
            ->withRandomPollImpact($randomPollImpact)
            ->withDebateWinnersProjected($projectedStandings)
            ->withDebateLosersProjected(array_reverse($projectedStandings))
        ;
    }

    public function getRcpApprovalProjections($pi_contest_id)
    {
        $contest = PiContest::find($pi_contest_id);
        $recent_polls = $contest->rcp_contest_polls()->orderBy('date_end', 'desc')->get()->take(10);
        return view('m.rcp.approval.projections')
            ->withContest($contest)
            ->withRecentPolls($recent_polls)
            ->withObamaValues($contest->contestValuesObama())
            ->withLastUpdate($contest->last_rcp_update())
            ->withOtherPollsters($contest->rcp_contest_pollsters()->orderBy('next_poll_expected', 'asc')->get())
        ;
    }

    /**
     * @todo: Check that this works (Obama etc). Logic moved to scrape/update class that discovers drop.
     */
    public function updateRcpLengthForPoll()
    {
        // $all_polls = RcpContestPoll::all();
        // foreach ($all_polls as $poll) {
        //     if ($poll->last_add && $poll->last_drop) {
        //         $add = new DateTime($poll->last_add->rcp_update->local_rcp_timestamp('Y-m-d H:i:s'));
        //         $drop = new DateTime($poll->last_drop->rcp_update->local_rcp_timestamp('Y-m-d H:i:s'));
        //         $length_in_average = $add->diff($drop);
        //         $poll->length_in_average = $length_in_average->format('%a days %h hours');
        //         $poll->save();
        //     }
        // }
    }

    public function getRcpPurge()
    {
        // $rc = MichaObamaScrape::where('id', '>', 0)->delete();
        // $rc = RcpUpdatePollster::where('id', '>', 0)->delete();
        // $rc = RcpUpdateAdd::where('id', '>', 0)->delete();
        // $rc = RcpUpdateDrop::where('id', '>', 0)->delete();
        // $rc = RcpUpdate::where('id', '>', 0)->delete();
        // $rc = RcpDay::where('id', '>', 0)->delete();
        // $rc = RcpScrape::where('id', '>', 0)->delete();
        // $rc = RcpContestPoll::where('id', '>', 0)->delete();
        // $rc = RcpContestPollster::where('id', '>', 0)->delete();
    }
}
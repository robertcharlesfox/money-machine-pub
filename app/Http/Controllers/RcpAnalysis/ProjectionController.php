<?php

use Smalot\PdfParser\Parser;
use Illuminate\Http\Request;

class ProjectionController extends Controller {

    protected $likelihoods = [
        'Probable' => 95,
        'Usually' => 80,
        'Toss-Up' => 50,
        'Doubtful' => 20,
        'Unlikely' => 5,
        'Final' => 0,
    ];

    protected $candidate_names = [
        'Clinton',
        'Trump',
        'Johnson',
        'Stein',
        'spread',
    ];

    public function getDropAnalysis()
    {
        return view('m.rcp.dropanalysis.index')
            ->withUpdates([])
        ;
    }

    public function postDropAnalysis(Request $request)
    {
        switch ($request->input('drop_type')) {
            case 'adds_greater':
                $updates = RcpUpdate::where('count_adds', '>', 0);
                break;
            
            default:
                $updates = RcpUpdate::where('count_drops', '>', 0);
                break;
        }
        
        // Set Contest ID if applicable.
        if ($request->input('pi_contest_id') != 'all') {
            $updates = $updates->where('pi_contest_id', '=', $request->input('pi_contest_id'));
        }
        // Exclude the Dem/GOP nominee polls.
        $updates = $updates->where('pi_contest_id', '<>', 12);
        $updates = $updates->where('pi_contest_id', '<>', 13);

        // Set more parameters based on inputs.
        switch ($request->input('drop_type')) {
            case 'drops_alone':
                $updates = $updates->where('count_adds', '=', 0);
                break;
            case 'swapouts':
                $updates = $updates->where('count_adds', '>', 0);
                break;
        }

        // Order the results.
        switch ($request->input('sort_by')) {
            case 'date':
                $updates = $updates->orderBy('rcp_timestamp', 'desc');
                break;
            case 'contest':
                $updates = $updates->orderBy('pi_contest_id', 'asc');
                break;
        }

        $updates = $updates->get();

        // Filter results.
        if ($request->input('day_of_week') != 'all') {
            $weekday = $request->input('day_of_week');
            $updates = $updates->filter(function ($update) use ($weekday) {
                return $weekday == date('l', strtotime($update->rcp_timestamp));
            });
        }
        switch ($request->input('drop_type')) {
            case 'drops_alone':
                // Already handled this by setting adds = 0
                break;
            case 'drops_greater':
                $updates = $updates->filter(function ($update) {
                    return $update->count_adds < $update->count_drops;
                });
                break;
            case 'adds_greater':
                $updates = $updates->filter(function ($update) {
                    return $update->count_adds > $update->count_drops;
                });
                break;
            case 'swapouts':
                // adds > 0 but the pollster ids do not match, we have a swapout.
                $updates = $updates->filter(function ($update) {
                    $adds = $update->rcp_update_adds()->orderBy('rcp_contest_pollster_id', 'asc')->get()->pluck('rcp_contest_pollster_id')->toArray();
                    $drops = $update->rcp_update_drops()->orderBy('rcp_contest_pollster_id', 'asc')->get()->pluck('rcp_contest_pollster_id')->toArray();
                    $new_adds = [];
                    $new_drops = [];
                    foreach ($adds as $add) {
                        if (!in_array($add, $drops)) {
                            $new_adds[] = $add;
                        }
                    }
                    foreach ($drops as $drop) {
                        if (!in_array($drop, $adds)) {
                            $new_drops[] = $drop;
                        }
                    }
                    return $new_adds && $new_drops;
                });
                break;
            case 'all':
                // Includes anything where drops === adds (compare array of pollster ids)
                $updates = $updates->filter(function ($update) {
                    $adds = $update->rcp_update_adds()->orderBy('rcp_contest_pollster_id', 'asc')->get()->pluck('rcp_contest_pollster_id')->toArray();
                    $drops = $update->rcp_update_drops()->orderBy('rcp_contest_pollster_id', 'asc')->get()->pluck('rcp_contest_pollster_id')->toArray();
                    $new_adds = [];
                    $new_drops = [];
                    foreach ($adds as $add) {
                        if (!in_array($add, $drops)) {
                            $new_adds[] = $add;
                        }
                    }
                    foreach ($drops as $drop) {
                        if (!in_array($drop, $adds)) {
                            $new_drops[] = $drop;
                        }
                    }
                    return ($new_adds && $new_drops) || ($update->count_adds < $update->count_drops);
                });
                break;
        }

        // Now we finally have our ultimate result set.
        // Put it and associated values in an array and return.
        $result_array = [];
        foreach ($updates as $update) {
            $previous_update = $update->previousUpdate();
            $result['headline'] = date('l n/j/y g:i a', strtotime($update->rcp_timestamp)) . ', ' . $update->pi_contest->name;
            $result['outcomes'] = $update->count_adds . ' Adds, ' . $update->count_drops . ' Drops';
            if ($update->percent_approval) {
                $result['rcp_averages'] = $previous_update->percent_approval . ' --> ' . $update->percent_approval;
                $change = $update->percent_approval - $previous_update->percent_approval;
                $result['rcp_change'] = $change >= 0 ? '+' . $change : $change;
            } else {
                $this_spread = $update->Clinton - $update->Trump;
                $previous_spread = $previous_update->Clinton - $previous_update->Trump;
                $result['rcp_averages'] = $previous_spread . ' --> ' . $this_spread;
                $change = $this_spread - $previous_spread;
                $result['rcp_change'] = $change >= 0 ? '+' . $change : $change;
            }
            $result['this_update']['timestamp'] = date('l n/j/y g:i a', strtotime($update->rcp_timestamp));
            $result['this_update']['update'] = $update;
            $result['this_update']['pollsters'] = $this->buildPollsterArray($update);
            $result['previous_update']['timestamp'] = date('l n/j/y g:i a', strtotime($previous_update->rcp_timestamp));
            $result['previous_update']['update'] = $previous_update;
            $result['previous_update']['pollsters'] = $this->buildPollsterArray($previous_update);

            $result_array[] = $result;
        }
        return view('m.rcp.dropanalysis.index')
            ->withUpdates($result_array)
        ;
    }

    private function buildPollsterArray($update)
    {
        $pollsters = [];
        foreach ($update->rcp_update_pollsters as $update_pollster) {
            $pollster = $update_pollster->rcp_contest_pollster;
            $poll = $update_pollster->rcp_contest_poll;
            $pollster_array['name'] = $pollster->name;
            $pollster_array['dates'] = date('n/j', strtotime($poll->date_start)) . ' - ' . date('n/j', strtotime($poll->date_end));
            $pollster_array['sample'] = $poll->sample;
            $pollster_array['age'] = (int) ((strtotime($update->rcp_timestamp) - strtotime($poll->date_start))/60/60/24);
            $pollsters[] = $pollster_array;
        }
        return $pollsters;
    }

    /**
     * 
     */
    public function getTvCAnalysis($contest_id)
    {
        // $n = new Navigator();
        // $t = new Trader();
        // $n->visitQuestionMarket($this->pi_question, 'nazgul-' . $this->id);
        // $t->nazgulPrepareTrade($this, $n->driver);
        // $n->makeDriver('', 'nazgul-' . $this->id);
        // $t->nazgulReleaseTrade($this, $n->driver);

        // die();
        $contest = PiContest::find($contest_id);
        $current_contest_values = $contest->getCurrentContestValues();
        $rcp_update = $contest->last_rcp_update();
        $candidates = $this->getValidCandidates($rcp_update);

        return view('m.rcp.tvc.projections')
            ->withLikelihoods($this->likelihoods)
            ->withCandidateNames($candidates)
            ->withContest($contest)
            ->withContestValues($current_contest_values)
            ->withLastRcpUpdate($rcp_update)
            ->withAjaxToken(csrf_token())
        ;
    }

    public function getFavorables($contest_id)
    {
        $contest = PiContest::find($contest_id);
        $current_contest_values = $contest->getCurrentContestValues();
        $rcp_update = $contest->last_rcp_update();

        return view('m.rcp.tvc.projections')
            ->withLikelihoods($this->likelihoods)
            ->withCandidateNames(['percent_favor'])
            ->withContest($contest)
            ->withContestValues($current_contest_values)
            ->withLastRcpUpdate($rcp_update)
            ->withAjaxToken(csrf_token())
        ;
    }

    private function getValidCandidates(RcpUpdate $rcp_update)
    {
        $valid_names = [];
        foreach ($this->candidate_names as $name) {
            if ($rcp_update->$name) {
                $valid_names[] = $name;
            }
        }
        return $valid_names;
    }

    public function readPdf($value='')
    {
        $file_location = '/Users/ted/Desktop/Topline Results-7319.pdf';

        // Parse pdf file and build necessary objects.
        $parser = new Parser();
        $pdf    = $parser->parseFile($file_location);

        $text = $pdf->getText();
        echo $text;
        d($text);
    }

}

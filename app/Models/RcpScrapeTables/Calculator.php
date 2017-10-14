<?php

class Calculator extends Eloquent {

    public function debateWinners(RcpUpdate $rcp_update, $candidate_update)
    {
        $winnerCandidates = $candidate_update->debate_candidates;
        $candidates = array();
        foreach ($winnerCandidates as $candidate => $threshold) {
            $candidates[$candidate]['name'] = $candidate;
            $candidates[$candidate]['value'] = $this->debateProjections($rcp_update, $candidate, $threshold, true);
            $candidates[$candidate]['text'] = $this->debateProjections($rcp_update, $candidate, $threshold);
        }

        $array = array_values(array_sort($candidates, function ($value) {
            return $value['value'];
        }));

        return array_reverse($array);
    }

    public function randomPollImpact(RcpUpdate $rcp_update, $candidate_update)
    {
        $winnerCandidates = $candidate_update->debate_candidates;
        $candidates = array();
        foreach ($winnerCandidates as $candidate => $threshold) {
            $candidates[$candidate]['name'] = $candidate;
            $candidates[$candidate]['value'] = $rcp_update->randomPoll($candidate, $threshold, true);
            $candidates[$candidate]['text'] = $rcp_update->randomPoll($candidate, $threshold);
        }

        $array = array_values(array_sort($candidates, function ($value) {
            return $value['value'];
        }));

        return array_reverse($array);
    }

    public function debateWinnersProjected(RcpUpdate $rcp_update, $candidate_update)
    {
        $winnerCandidates = $candidate_update->debate_candidates;
        $candidates = array();
        foreach ($winnerCandidates as $candidate => $threshold) {
            $candidates[$candidate]['name'] = $candidate;
            $candidates[$candidate]['value'] = $this->debateProjections($rcp_update, $candidate, $threshold, true, true);
            $candidates[$candidate]['text'] = $this->debateProjections($rcp_update, $candidate, $threshold, false, true);
        }

        $array = array_values(array_sort($candidates, function ($value) {
            return $value['value'];
        }));

        return array_reverse($array);
    }

    /**
     * Average of RCP Finals, Un-included results, and Projections.
     */
    public function debateProjections($rcp_update, $candidate = '', $threshold = '', $value_only = false, $include_randoms = false)
    {
        $poll_values = array();

        foreach ($rcp_update->debate_pollsters() as $pollster) {
            if ($pollster->is_likely_final_for_week) {
                $poll_values[] = $pollster->avgInclusionValue($candidate);
            }
            else {
                $poll_values[] = $pollster->trendForecast($rcp_update->recent_polls($candidate), $candidate);
            }
        }

        if ($include_randoms) {
            for ($i=0; $i < $rcp_update->pi_contest->random_polls_to_add; $i++) { 
                $poll_values[] = $rcp_update->randomPoll($candidate, 0, true);
            }
        }
    
        if (count($poll_values)) {
            $avg = number_format((array_sum($poll_values) / count($poll_values)), 1);
            $avg = $threshold ? number_format($avg - $threshold, 1) : $avg;
            $stdev = number_format($rcp_update->totalStDev($candidate, $include_randoms), 1);
            $range = ' (' . ($avg - $stdev) . ' - ' . ($avg + $stdev) . ') ';
            return $value_only ? $avg : $avg . ' ± ' . $stdev . $range;
        }
        return 'No Polls';
    }
}
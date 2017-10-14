<?php

class MichanikosController extends Controller {

    public function getMichanikos($poll_id)
    {
        $scraper = new Scraper('http://spinnernicholas.com/rcp/scraper2/json/?p=' . $poll_id);
        $json_polls = json_decode($scraper->html);
        foreach ($json_polls as $poll) {
            $rcp_update = $this->getRcpUpdate($poll, $this->getContestId($poll_id));
            $rcp_update->processMichaPoll($poll);
        }

        $rcp_updates = RcpUpdate::all();
        foreach ($rcp_updates as $rcp_update) {
            $rcp_update->setDateRange();
        }
        return;
    }

    private function getContestId($poll_id)
    {
        switch ($poll_id) {
            case 1: return 1;
            case 2: return 8;
            case 3: return 12;
            case 4: return 13;
        }
    }

    private function getRcpUpdate($poll, $pi_contest_id)
    {
        $date = date('Y-m-d', strtotime($poll->timestamp));
        $rcp_day = RcpDay::where('rcp_date', '=', $date)->first();
        if ( ! $rcp_day) {
            $rcp_day = new RcpDay();
            $rcp_day->rcp_date = $date;
            $rcp_day->save();
        }

        if ($rcp_day->rcp_updates()->where('pi_contest_id', '=', $pi_contest_id)->count()) {
            foreach ($rcp_day->rcp_updates()->where('pi_contest_id', '=', $pi_contest_id)->get() as $update) {
                if ((strtotime($poll->timestamp) - strtotime($update->rcp_timestamp)) < 60) {
                    return $update;
                }
            }
        }

        $last_update = RcpUpdate::where('pi_contest_id', '=', $pi_contest_id)
            ->orderBy('id', 'desc')
            ->first()
        ;

        $rcp_update = new RcpUpdate();
        $rcp_update->rcp_time = date('H:i',strtotime($poll->timestamp));
        $rcp_update->rcp_timestamp = $poll->timestamp;
        $rcp_update->pi_contest_id = $pi_contest_id;
        $rcp_update->rcp_day_id = $rcp_day->id;

        switch ($pi_contest_id) {
            case 1: 
            case 8:
                $rcp_update->percent_approval = $poll->rcp1;
                $rcp_update->percent_disapproval = $poll->rcp2;
                break;
            
            case 12:
                $rcp_update->Clinton = $poll->rcp1;
                $rcp_update->Sanders = $poll->rcp2;
                break;
            
            case 13:
                $rcp_update->Trump = $poll->rcp1;
                // $rcp_update->Carson = $poll->rcp2;
                break;
        }

        $rcp_update->save();
        $rcp_update->makeUpdatePollsters($last_update);

        return $rcp_update;
    }


    private function analyzeMichanikos($json_polls)
    {
        $micha_polls = array();
        $polls_by_timestamp = array();
        $timestamps_by_day = array();
        $polls_by_day = array();
        $polls_by_day_of_week = array();
        $timestamps_by_day_of_week = array();
        $polls_type_day_of_week = array();
        foreach ($json_polls as $poll) {
            $weekday = date('l', strtotime($poll->timestamp));
            $day_display = date('l m-d', strtotime($poll->timestamp));
            
            // The original. Show things by type, then timestamp. See how many of the same thing happened on a timestamp.
            $micha_polls[$poll->type][$poll->timestamp][] = $poll;
            
            // See how many of everything happened on a timestamp.
            $polls_by_timestamp[$poll->timestamp][] = $poll;

            // See how many total unique timestamps happened on a single day.
            $timestamps_by_day[$day_display][$poll->timestamp][] = $poll;

            // See how many individual polling events happened on a single day.
            $polls_by_day[$day_display][] = $poll;

            // See how many polling events happened on each day of the week.
            $polls_by_day_of_week[$weekday][] = $poll;

            // See how many timestamps happened on each day of the week.
            $timestamps_by_day_of_week[$weekday][$poll->timestamp][] = $poll;

            // See how many poll events by type happened on each day of the week.
            $polls_type_day_of_week[$weekday][$poll->type][] = $poll;
        }
        d($micha_polls, $polls_by_timestamp, $timestamps_by_day, $polls_by_day, $polls_by_day_of_week, $timestamps_by_day_of_week, $polls_type_day_of_week);
        die();
    }
}
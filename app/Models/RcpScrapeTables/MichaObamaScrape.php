<?php

class MichaObamaScrape extends Eloquent {

    public function rcp_contest_pollster()
    {
        return $this->belongsTo(RcpContestPollster::class);
    }

    public function rcp_contest_poll()
    {
        return $this->belongsTo(RcpContestPoll::class);
    }

    public function saveMichaObamaScrape($poll)
    {
        $weekday = date('l', strtotime($poll->timestamp));
        $day_of_timestamp = date('Y-m-d', strtotime($poll->timestamp));

        $this->pollster_name = $poll->poll;
        $this->micha_timestamp = $poll->timestamp;
        $this->date_range = $poll->dates;
        $this->event_type = $poll->type;
        $this->percent_approval = $poll->v1;
        $this->percent_disapproval = $poll->v2;
        $this->rcp_avg_approval = $poll->rcp1;
        $this->rcp_avg_disapproval = $poll->rcp2;

        $date_start = substr($poll->dates, 0, strpos($poll->dates, '-') - 1);
        $date_end = substr($poll->dates, strpos($poll->dates, '-') + 2);
        $date_start_fixed = date('Y-m-d', strtotime($date_start . '/2015'));
        $date_end_fixed = date('Y-m-d', strtotime($date_end . '/2015'));
        $this->date_start = $date_start_fixed;
        $this->date_end = $date_end_fixed;

        $this->rcp_contest_pollster_id = $this->findRcpContestPollster($poll);
        $this->rcp_contest_poll_id = $this->findRcpContestPoll();

        if ($poll->type == 'add' || $poll->type == 'update') {
            $this->date_added_to_rcp_average = $day_of_timestamp;
            $this->day_of_week_added_to_rcp = $weekday;
        }

        $this->save();
    }

    public function updateMichaObamaScrape($poll)
    {
        $weekday = date('l', strtotime($poll->timestamp));
        $day_of_timestamp = date('Y-m-d', strtotime($poll->timestamp));
        $first = new DateTime($this->date_end);
        $last = new DateTime($day_of_timestamp);

        $this->date_dropped_from_rcp_average = $day_of_timestamp;
        $this->day_of_week_dropped_from_rcp = $weekday;
        $this->age_of_poll_when_dropped_from_rcp = $first->diff($last)->format('%r%a');
        $this->save();
    }

    private function findRcpContestPollster($micha_poll)
    {
        // Remove asterisks. Also swap hyphens for slashes
        $pollster_name = $micha_poll->poll;
        $pollster_name = str_replace('*', '', $pollster_name);
        $pollster_name = str_replace('-', '/', $pollster_name);

        // A matching Contest Pollster will have the same name and the same contest (1 for Obama Approval).
        $pollster = RcpContestPollster::where('name', '=', $pollster_name)
            ->where('pi_contest_id', '=', 1)
            ->first()
        ;
        if ( ! $pollster) {
            $pollster = new RcpContestPollster();
            $pollster->pi_contest_id = 1;
            $pollster->name = $pollster_name;
            $pollster->save();
        }
        return $pollster->id;
    }

    private function findRcpContestPoll()
    {
        $poll = RcpContestPoll::where('rcp_contest_pollster_id', '=', $this->rcp_contest_pollster_id)
            ->where('date_start', '=', $this->date_start)
            ->where('date_end', '=', $this->date_end)
            ->where('percent_favor', '=', $this->percent_approval)
            ->where('percent_against', '=', $this->percent_disapproval)
            ->first()
        ;
        if ( ! $poll) {
            $poll = new RcpContestPoll();
            $poll->rcp_contest_pollster_id = $this->rcp_contest_pollster_id;
            $poll->date_start = $this->date_start;
            $poll->date_end = $this->date_end;
            $poll->percent_favor = $this->percent_approval;
            $poll->percent_against = $this->percent_disapproval;
            $poll->save();
        }
        return $poll->id;
    }
}
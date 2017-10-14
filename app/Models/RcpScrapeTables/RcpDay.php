<?php

class RcpDay extends Eloquent {

    public function rcp_updates()
    {
        return $this->hasMany(RcpUpdate::class);
    }

    public function contestUpdates($pi_contest_id)
    {
        return $this->rcp_updates()->where('pi_contest_id', '=', $pi_contest_id)->get();
    }

    public function isFriday()
    {
        if (date('l', strtotime($this->rcp_date)) == 'Friday') {
            return true;
        }
        return false;
    }

    public function updateSummary($pi_contest_id)
    {
        $total = $this->contestUpdates($pi_contest_id)->count() . ' Updates, Final one at ';
        $last = $this->contestUpdates($pi_contest_id)->last();
        $last_update = $last->local_rcp_timestamp('g:i a') . ', including ';
        $last_update_adds = $last->count_adds . ' adds, ';
        $last_update_drops = $last->count_drops . ' drops, and ';
        $last_update_pollsters = $last->count_pollsters . ' pollsters: ';
        $oldest_poll = 'oldest is ' . $last->oldest_poll . ', second-oldest is ' . $last->second_oldest_poll;
        $length = ', date range is ' . $last->date_range_length;
        return date('l', strtotime($this->rcp_date)) . ' ' . $total . $last_update . $last_update_adds . $last_update_drops . $last_update_pollsters . $oldest_poll . $length;
    }
}
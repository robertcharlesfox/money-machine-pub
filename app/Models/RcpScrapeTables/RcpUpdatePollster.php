<?php

class RcpUpdatePollster extends Eloquent {

    public function rcp_contest_pollster()
    {
        return $this->belongsTo(RcpContestPollster::class);
    }

    public function rcp_contest_poll()
    {
        return $this->belongsTo(RcpContestPoll::class);
    }

    public function rcp_update()
    {
        return $this->belongsTo(RcpUpdate::class);
    }

    public function saveNewFromScrape($rcp_update_id, $rcp_contest_pollster_id, $rcp_contest_poll_id)
    {
        $this->rcp_update_id = $rcp_update_id;
        $this->rcp_contest_pollster_id = $rcp_contest_pollster_id;
        $this->rcp_contest_poll_id = $rcp_contest_poll_id;
        $this->save();
    }
}
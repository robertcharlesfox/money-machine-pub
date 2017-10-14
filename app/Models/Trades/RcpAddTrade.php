<?php

class RcpAddTrade extends Eloquent {

    public function pi_contest()
    {
        return $this->belongsTo(PiContest::class);
    }

    public function pi_question()
    {
        return $this->belongsTo(PiQuestion::class);
    }

    public function rcp_contest_pollster_name()
    {
        return RcpContestPollster::find($this->rcp_contest_pollster_id)->name;
    }

    public function rcp_update()
    {
        return $this->belongsTo(RcpUpdate::class);
    }

    public function deactivate()
    {
        $this->active = 0;
        $this->auto_trade_me = 0;
        $this->save();
    }

    public function saveAddTradeDefinition($input)
    {
        $this->active = 1;
        $this->pi_contest_id = $input['pi_contest_id'];
        $this->pi_question_id = $input['pi_question_id'];
        $this->rcp_contest_pollster_id = $input['rcp_contest_pollster_id'];
        $this->poll_result = $input['poll_result'];
        $this->save();
    }

    public function saveAddTradeValues($input)
    {
        $this->buy_or_sell = $input['buy_or_sell'];
        $this->yes_or_no = $input['yes_or_no'];
        $this->price = $input['price'];
        $this->shares = $input['shares'];
        $this->auto_trade_me = isset($input['auto_trade_me']) ? $input['auto_trade_me'] : 0;
        $this->save();
    }
}
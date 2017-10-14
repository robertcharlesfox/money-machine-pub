<?php

class RcpDropTrade extends Eloquent {

    public function pi_contest()
    {
        return $this->belongsTo(PiContest::class);
    }

    public function pi_question()
    {
        return $this->belongsTo(PiQuestion::class);
    }

    public function rcp_contest_pollster_1_name()
    {
        return RcpContestPollster::find($this->rcp_contest_pollster_id_1)->name;
    }

    public function rcp_contest_pollster_2_name()
    {
        return $this->rcp_contest_pollster_id_2 ? '& ' . RcpContestPollster::find($this->rcp_contest_pollster_id_2)->name : '';
    }

    public function rcp_contest_pollster_3_name()
    {
        return $this->rcp_contest_pollster_id_3 ? '& ' . RcpContestPollster::find($this->rcp_contest_pollster_id_3)->name : '';
    }

    public function rcp_contest_pollster_4_name()
    {
        return $this->rcp_contest_pollster_id_4 ? '& ' . RcpContestPollster::find($this->rcp_contest_pollster_id_4)->name : '';
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

    public function saveDropTradeDefinition($input)
    {
        $this->active = 1;
        $this->auto_trade_me = isset($input['auto_trade_me']) ? $input['auto_trade_me'] : 0;
        $this->pi_contest_id = $input['pi_contest_id'];
        $this->pi_question_id = $input['pi_question_id'];
        $this->rcp_contest_pollster_id_1 = $input['rcp_contest_pollster_id_1'];
        
        // Pollsters 2-4 not required.
        if (isset($input['rcp_contest_pollster_id_2'])) {
            $this->rcp_contest_pollster_id_2 = $input['rcp_contest_pollster_id_2'];
            if (isset($input['rcp_contest_pollster_id_3'])) {
                $this->rcp_contest_pollster_id_3 = $input['rcp_contest_pollster_id_3'];
                if (isset($input['rcp_contest_pollster_id_4'])) {
                    $this->rcp_contest_pollster_id_4 = $input['rcp_contest_pollster_id_4'];
                }
            }
        }

        $this->save();
    }

    public function saveDropTradeValues($input)
    {
        $this->buy_or_sell = $input['buy_or_sell'];
        $this->yes_or_no = $input['yes_or_no'];
        $this->price = $input['price'];
        $this->shares = $input['shares'];
        $this->auto_trade_me = isset($input['auto_trade_me']) ? $input['auto_trade_me'] : 0;
        $this->save();
    }
}
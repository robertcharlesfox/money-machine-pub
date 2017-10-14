<?php

use Sunra\PhpSimple\HtmlDomParser;

class PiQuestion extends Eloquent
{
    public function saveQuestionMini($input)
    {
        $input_array = array();
        $input_array['url_of_market'] = $input['url_of_answer'];
        $input_array['pi_contest_id'] = 163;

        $this->saveQuestion($input_array);
    }

    public function saveQuestion($input)
    {
        $operator = new BotPiOperatorNonAuth();
        $dom = $operator->getPiUrlNonAuth($input['url_of_market'], 'data1');

        $header = $dom->find('div[class=container] h2', 0);
        if ($header) {
            $header = $dom->find('div[class=container] h2', 0)->plaintext;
        }
        else {
            $header = $dom->find('div[class=container] h1', 0)->plaintext;
        }
        $question_details = $dom->find('table tbody', 0);
        $question_ticker = $question_details->find('tr', 0)->find('td', 1)->plaintext;

        if ($question_details->find('tr', 1)->find('td', 0)->plaintext == 'Market Type:') {
            $date_open = $question_details->find('tr', 2)->find('td', 1)->plaintext;
            $date_close = $question_details->find('tr', 3)->find('td', 1)->plaintext;
        }
        else {
            $date_open = $question_details->find('tr', 1)->find('td', 1)->plaintext;
            $date_close = $question_details->find('tr', 2)->find('td', 1)->plaintext;
        }
        $date_open_fixed = date('Y-m-d', strtotime($date_open));
        $date_close_fixed = date('Y-m-d', strtotime($date_close));
        $ts_close = date('Y-m-d H:i:s', strtotime($date_close . ' +1day'));

        $this->question_text = $header;
        $this->question_ticker = $question_ticker;
        $this->date_open = $date_open_fixed;
        $this->date_close = $date_close_fixed;
        $this->ts_contract_closes = $ts_close;
        $this->category = $this->category ? $this->category : 'default';

        $this->pi_contest_id = $input['pi_contest_id'];
        $this->url_of_market = $input['url_of_market'];
        $this->auto_trade_me = 1;

        $this->save();

        $dom->clear();
        unset($dom);
    }

    public function saveCompetitionQuestion($input)
    {
        $this->chance_to_win = $input['chance_to_win'];
        $this->auto_trade_me = isset($input['auto_trade_me']) ? 1 : 0;
        $this->category = $input['category'];
        $this->max_shares_owned = $input['max_shares_owned'];
        $this->min_shares_owned = $input['min_shares_owned'];
        $this->fundraising_high = $input['fundraising_high'];
        $this->fundraising_low = $input['fundraising_low'];
        $this->churn_range = $input['churn_range'];
        $this->save();
    }

    public function activate()
    {
        $this->active = 1;
        $this->auto_trade_me = 1;
        $this->save();
    }
    
    public function deactivate()
    {
        $this->active = 0;
        $this->auto_trade_me = 0;
        $this->save();
    }

    public function getAutotradeAttribute()
    {
        if ( ! $this->auto_trade_me) {
            return 'None';
        }
        $buy = 'Buy ' . $this->buy_shares . '@' . $this->buy_price;
        $sell = 'Sell ' . $this->sell_shares . '@' . $this->sell_price;
        return $this->yes_or_no . ': ' . $buy . ', ' . $sell;
    }

    public function pi_contest()
    {
        return $this->belongsTo(PiContest::class);
    }

    public function pi_markets()
    {
        return $this->hasMany(PiMarket::class);
    }

    public function nazguls()
    {
        return $this->hasMany(Nazgul::class);
    }

    public function executed_autotrades()
    {
        return $this->hasMany(ExecutedAutotrade::class);
    }

    public function rcp_drop_trades()
    {
        return $this->hasMany(RcpDropTrade::class);
    }

    public function rcp_add_trades()
    {
        return $this->hasMany(RcpAddTrade::class);
    }
}

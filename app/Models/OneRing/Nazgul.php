<?php

use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\SendTextEmail;
use PredictIt\Trader;
use PredictIt\Navigator;
use PredictIt\Scraper;

class Nazgul extends Eloquent {
    use DispatchesJobs;

    private function navigatorTrader($operation)
    {
        $n = new Navigator();
        $t = new Trader();
        $s = new Scraper();
        switch ($operation) {
            case 'awaken_trade':
                $html = $n->visitQuestionMarket($this->pi_question, 'nazgul-' . $this->id);
                $t->nazgulPrepareTrade($this, $n->driver);
                $s->logState($this, $html);
                break;
            case 'awaken_cancel':
                // Don't get too clever and mess with the popup. Just go to the cancel tab and wait.
                $n->visitQuestionMarket($this->pi_question, 'nazgul-' . $this->id . '-cancel');
                $t->nazgulPrepareCancelOrders($n->driver);
                break;
            case 'release_trade':
                $n->makeDriver('', 'nazgul-' . $this->id);
                $t->nazgulReleaseTrade($this, $n->driver);
                $this->executed = 1;
                $this->active = 0;
                $this->auto_trade_me = 0;
                $this->save();
                Cache::put('status-nazgul-' . $this->id, 'executed', 120);
                break;
            case 'release_cancel':
                $n->makeDriver('', 'nazgul-' . $this->id . '-cancel');
                $t->nazgulReleaseCancelOrders($n->driver);
                $this->cancel_first = 0;
                $this->save();
                break;
        }
    }

    public function awaken()
    {
        if (Cache::get('status-nazgul-' . $this->id) != 'executed') {
            if ($this->cancel_first) {
                $this->navigatorTrader('awaken_cancel');
            }
            $this->navigatorTrader('awaken_trade');
        } else {
            // Scrape the result. Deactivate/delete from cache.
        }
    }

    public function ravage()
    {
        // In case this Nazgul is called by a second update, abort if already executed.
        if (!$this->active) {
            return;
        }
        if ($this->cancel_first) {
            $this->navigatorTrader('release_cancel');
        }
        if ($this->auto_trade_me) {
            $this->navigatorTrader('release_trade');
        }
    }

    public function vanish()
    {
        $this->navigatorTrader('release_cancel');
    }

    public function pi_contest()
    {
        return $this->belongsTo(PiContest::class);
    }

    public function pi_question()
    {
        return $this->belongsTo(PiQuestion::class);
    }

    public function pi_market()
    {
        return $this->belongsTo(PiMarket::class);
    }

    public function saveDetails($input)
    {
        $this->auto_trade_me = isset($input['auto_trade_me']) ? $input['auto_trade_me'] : 0;
        $this->cancel_first = isset($input['cancel_first']) ? $input['cancel_first'] : 0;
        $this->buy_or_sell = $input['buy_or_sell'];
        $this->yes_or_no = $input['yes_or_no'];
        $this->price_limit = $input['price_limit'];
        $this->risk = $input['risk'];
        $this->save();
    }
}
<?php namespace PredictIt;

use Sunra\PhpSimple\HtmlDomParser;
use PiContest;
use Log;

class CompetitionTrader
{
    public function runCompetition(PiContest $contest, $candidate_values, $max_risk, $urgency)
    {
        // visit a competition page
        $nav = new Navigator();
        $page = $nav->visitCompetitionPage($contest);
        if (!$page) {
            return;
        }
    
        // Get the data landscape
        $scraper = new Scraper();
        $data = $scraper->getCompetitionPageData($page);
        if (!$data) {
            return;
        }

        // Match the contracts to the bracket analysis. Figure out what to trade
        $analyst = new Analyst();
        $outcomes = $analyst->findBiggestChanges($contest, $candidate_values, $data);
        if (!$outcomes) {
            return;
        }

        // place orders
        if ($this->checkReadyToTrade($contest, $outcomes)) {
            $this->createOrders($contest, $outcomes, $max_risk, $urgency, $nav);
        }

        // @todo: "recursive re-run" if urgency is high
        // @todo: see if you have bought enough, then buy more.
        // @todo: see if you want to now put in accumulation orders, if liquidation is complete.
        if ($urgency == 1) {
            // OR JUST RE-RUN IT FROM THE ScrapeFundraising CLASS!!!
        }

        // Lastly, Place sell orders at higher price for whatever you've accumulated, sell into excess movement.
    }

    /**
     * Validate 2 ways before placing a trade.
     * 1- Auto-trade has been set to ON.
     * 2- The entire contest's Competition Total == 100.
     */
    protected function checkReadyToTrade(PiContest $contest, $outcomes)
    {
        if ($contest->auto_trade_this_contest &&
            $contest->competition_total >= 95 &&
            $contest->competition_total <= 105) 
        {
            return true;
        }
        return false;
    }

    private function createOrders(PiContest $contest, $outcomes, $max_risk, $urgency, $nav)
    {
        // Sort by which candidate had the most extreme movement.
        $outcomes = array_reverse(array_values(array_sort($outcomes, function ($value) {
            return abs($value['difference']);
        })));

          d($outcomes);
          die();

        $t = new Trader();
        $t->setDriver($nav->driver);
        foreach ($outcomes as $outcome) {
            if ($outcome['liquidation'] == true) {
                $this->handleLiquidationOrders($outcome, $max_risk, $urgency, $t);
            } else {
                $this->handleAccumulationOrders($outcome, $max_risk, $urgency, $t);
                if ($outcome['difference'] > 30) {
                    $this->handleAccumulationOrders($outcome, $max_risk, $urgency, $t);
                } elseif ($outcome['difference'] < -30) {
                    $this->handleAccumulationOrders($outcome, $max_risk, $urgency, $t);
                }
            }
        }
    }

    /**
     * If you're selling, cancel the pre-existing buy orders so you don't collide.
     * If you have only other sell orders, cancel them after liquidating.
     */
    private function handleLiquidationOrders($outcome, $max_risk, $urgency, $t)
    {
        $cancel_later = false;
        if ($outcome['my_shares']['buy_offers'] > 0) {
            $t->cancelCompetitionOrders($outcome['contract']['contract_id']);
        } elseif ($outcome['my_shares']['sell_offers'] > 0) {
            $cancel_later = true;
        }

        if ($outcome['my_shares']['quantity'] > 0) {
            $discount_bracket = 3;
            $discount = $discount_bracket * $urgency;
            $trade_price = $this->helperDetermineLiquidatePrice($outcome, $outcome['my_shares']['yes_or_no'], $discount);
            $trade_shares = min((int) ($max_risk / ($trade_price / 100)), $outcome['my_shares']['quantity']);
            // $trade_price = 1;
            Log::info($outcome['contract']['question_ticker'] . ' sell ' . $outcome['my_shares']['yes_or_no'] . $trade_shares . '@' . $trade_price);
            $t->placeCompetitionTrade('sell', $outcome['my_shares']['yes_or_no'], $trade_shares, $trade_price);
        }

        if ($cancel_later) {
            $t->cancelCompetitionOrders($outcome['contract']['contract_id']);
        }
    }

    /**
     * All systems go to accumulate shares in a contract.
     * Difference vs liquidation: here, Sky is the limit. Buy and re-buy as needed.
     */
    private function handleAccumulationOrders($outcome, $max_risk, $urgency, $t)
    {
        $yes_or_no = $this->helperDetermineAccumulateYesOrNo($outcome);
        $contract_id = $outcome['contract']['contract_id'];
        $discount_bracket = 3;
        $discount = $discount_bracket * $urgency;
        $trade_price = $this->helperDetermineAccumulatePrice($outcome, $yes_or_no, $discount);
        $trade_shares = (int) ($max_risk / ($trade_price / 100));
        // $trade_price = 1;
        Log::info($outcome['contract']['question_ticker'] . ' buy ' . $yes_or_no . $trade_shares . '@' . $trade_price);
        $t->placeCompetitionTrade('buy', $yes_or_no, $trade_shares, $trade_price, $contract_id);
    }

    private function helperDetermineAccumulateYesOrNo($outcome)
    {
        if ($outcome['my_shares']['yes_or_no']) {
            return $outcome['my_shares']['yes_or_no'];
        } else {
            $candidate_value = $outcome['new_value'];
            $buy_yes_price = $outcome['prices']['buy_yes'];
            $sell_yes_price = $outcome['prices']['sell_yes'];
            if ($candidate_value > $buy_yes_price) {
                return 'Yes';
            } elseif ($candidate_value < $sell_yes_price) {
                return 'No';
            } else {
                return 'No';
            }
        }
    }

    private function helperDetermineAccumulatePrice($outcome, $yes_or_no, $discount)
    {
        if ($yes_or_no == 'Yes') {
            return max($outcome['new_value'] - $discount, 1);
        }
        return max((99 - $outcome['new_value']) - $discount, 1);
    }

    private function helperDetermineLiquidatePrice($outcome, $yes_or_no, $discount)
    {
        if ($yes_or_no == 'Yes') {
            return max($outcome['new_value'] - $discount, 2);
        }
        return max((99 - $outcome['new_value']) - $discount, 2);
    }
}

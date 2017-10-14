<?php namespace PredictIt;

use PiContest;
use PiQuestion;

// define('DEFAULT_SHARES_IN_BLOCKING_BID', 40);
// define('DEFAULT_MAX_SHARES_TO_BUY', 400);
// define('DEFAULT_MIN_SHARES_TO_RETAIN', 0);
// define('DEFAULT_MAX_SHARES_OPEN_ORDERS', 150);
// define('DEFAULT_SHARES_PER_ORDER', 50);
// define('DEFAULT_CHURN_RANGE', 2);
// define('DEFAULT_PRICE_SPREAD', 9);

class Analyst extends Base
{
    public $m1 = 1; // Weight of my estimate
    public $m2 = 1; // Weight of market's opinion

    /**
     * Compare which PiQuestion's value changed the most after an event.
     * Return an array with analysis.
     * @todo: Update the chance_to_win on the Competition Question. (?)
     */
    public function findBiggestChanges(PiContest $contest, $candidate_values, $data)
    {
        $outcomes = [];
        foreach ($candidate_values as $contract_id => $candidate_value) {
            $market = $data[$contract_id];
            $market['new_value'] = $candidate_value;
            $buy_yes_price = $market['prices']['buy_yes'];
            $sell_yes_price = $market['prices']['sell_yes'];

            // @todo: needsCancellations()
            if ($candidate_value > $buy_yes_price) {
                $market['difference'] = $buy_yes_price - $candidate_value;
                $market['liquidation'] = $this->needsLiquidation('Yes', $market);
            } elseif ($candidate_value < $sell_yes_price) {
                $market['difference'] = $sell_yes_price - $candidate_value;
                $market['liquidation'] = $this->needsLiquidation('No', $market);
            } else {
                $market['difference'] = 0;
                $market['liquidation'] = false;
            }
            $outcomes[$contract_id] = $market;
        }
        return $outcomes;
    }

    /**
     * Check your owned shares. Do you need to liquidate or buy more?
     * If we own the opposite type of shares, return that we need to sell them.
     */
    protected function needsLiquidation($yes_or_no, $market)
    {
        if ($market['my_shares']['yes_or_no'] && $market['my_shares']['yes_or_no'] != $yes_or_no) {
            return true;
        }
        return false;
    }

    // @todo: normalize the market's opinion, based on looking at the market's opinion of every contract in this contest
    // regress it all back to 100
    // it's okay to assume that we have all the latest quotes - that will be part of my job
    // and it will self-correct with every new visit after then
    // although it will also be highly plausible to do a regular cancel-visit-trade script every so often
    // alternatively, a flag for when to clear out is if you have placed full 100% of your orders, but don't have ANY shares
    protected function helperChooseBestPrice($buy_or_sell, $trade, $prices)
    {
        $yes_value = (int) ((($trade->chance_to_win * $this->m1) + ($trade->cache_market_support_yes_side_price * $this->m2)) / ($this->m1 + $this->m2));
        $no_value = (int) ((((100 - $trade->chance_to_win) * $this->m1) + ($trade->cache_market_support_no_side_price * $this->m2)) / ($this->m1 + $this->m2));
        $churn_range = $trade->churn_range ? (int) ($trade->churn_range / 2) : DEFAULT_CHURN_RANGE;

        $buy_yes = $yes_value - $churn_range;
        $sell_yes = (100 - $no_value) + $churn_range;
        $buy_no = $no_value - $churn_range;
        $sell_no = (100 - $yes_value) + $churn_range;

        $valueDiscountPrice = $trade->yes_or_no == 'Yes' ? $buy_yes : $buy_no;
        $valuePremiumPrice = $trade->yes_or_no == 'Yes' ? $sell_yes : $sell_no;
        $valueDiscountPrice = max($valueDiscountPrice, 1);
        $valuePremiumPrice = min($valuePremiumPrice, 99);
        echo 'buy/sell value prices: ' . $valueDiscountPrice . '/' . $valuePremiumPrice . ' for ' . $trade->question_ticker . PHP_EOL;
        
        $inverse_buy_or_sell = $buy_or_sell == 'buy' ? 'sell' : 'buy';

        if ($trade->yes_or_no == 'Yes') {
            $orderType = $buy_or_sell . 'Yes';
            $otherOrderType = $inverse_buy_or_sell . 'Yes';
        }
        else {
            $orderType = $inverse_buy_or_sell . 'Yes';
            $otherOrderType = $buy_or_sell . 'Yes';
        }

        // If there are no shares for sale, this throws an error.
        // $offersToMe = $prices[$orderType];
        // $bestOfferPrice = $trade->yes_or_no == 'Yes' ? $offersToMe[0]['price'] : 100 - $offersToMe[0]['price'];
        
        if (isset($prices[$otherOrderType])) {
            $offersCompeting = $prices[$otherOrderType];
            $bestCompetingPrice = $trade->yes_or_no == 'Yes' ? $offersCompeting[0]['price'] + ($churn_range * 2) : 100 - $offersCompeting[0]['price'] + ($churn_range * 2);
            $competingShareSize = $trade->pi_contest->shares_in_blocking_bid ? $trade->pi_contest->shares_in_blocking_bid : DEFAULT_SHARES_IN_BLOCKING_BID;
            foreach ($offersCompeting as $bidder) {
                if ($bidder['shares'] > $competingShareSize) {
                    $bestCompetingPrice = $trade->yes_or_no == 'Yes' ? $bidder['price']: 100 - $bidder['price'];
                    break;
                }
            }
        }
        else {
            $bestCompetingPrice = $trade->yes_or_no == 'Yes' ? 0 : 100;
        }
    
        if ($this->beast_mode) {
            // When buying, use the average of the value and the competition.
            // When selling, use the valuePremium. Most likely will revise this or sell manually.
            return $buy_or_sell == 'buy' ? (int) (($bestCompetingPrice + $valueDiscountPrice) / 2) : $valuePremiumPrice;
        }

        if ($trade->cache_market_support_net_price_spread > DEFAULT_PRICE_SPREAD) {
            return $buy_or_sell == 'buy' ? min(($bestCompetingPrice + 1), $valueDiscountPrice) : $bestCompetingPrice - 1;
        }
        return $buy_or_sell == 'buy' ? min(($bestCompetingPrice + 1), $valueDiscountPrice) : (int) ((($bestCompetingPrice - 1) + $valuePremiumPrice) / 2);
        // return $buy_or_sell == 'buy' ? min(($bestCompetingPrice + 1), $valueDiscountPrice) : max(($bestCompetingPrice - 1), $valuePremiumPrice);
    }

    protected function helperChooseShares($buy_or_sell, $trade, $shares)
    {
        $orderType = ucfirst($buy_or_sell) . ' ' . $trade->yes_or_no;
        $myShares = isset($shares['myShares']) ? (int) $shares['myShares'] : 0;
        $offeredShares = ( ! isset($shares['myOffers'][$orderType])) ? 0 : $shares['myOffers'][$orderType];
        if ($offeredShares < DEFAULT_MAX_SHARES_OPEN_ORDERS) {
            if ($buy_or_sell == 'buy') {
                $totalShares = $myShares + $offeredShares;
                $maxShares = $trade->max_shares_owned ? $trade->max_shares_owned : DEFAULT_MAX_SHARES_TO_BUY;
                $buysAvailable = max(($maxShares - $totalShares), 0);
                $buyShares = $trade->buy_shares ? $trade->buy_shares : DEFAULT_SHARES_PER_ORDER;
                return min($buyShares, $buysAvailable);
            }
            else {
                $remainingShares = $myShares - $offeredShares;
                $minShares = $trade->min_shares_owned ? $trade->min_shares_owned : DEFAULT_MIN_SHARES_TO_RETAIN;
                $sellsAvailable = max(($remainingShares - $minShares), 0);
                $sellShares = $trade->sell_shares ? $trade->sell_shares : DEFAULT_SHARES_PER_ORDER;
                return min($sellShares, $sellsAvailable);
            }
        }
        else {
            return 0;
        }
    }
}

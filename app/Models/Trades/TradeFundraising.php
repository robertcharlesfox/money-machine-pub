<?php

class TradeFundraising extends TraderBot
{
    private $max_yes_price = 95;
    private $max_yes_shares = 600;
    private $max_no_price = 96;
    private $max_no_shares = 700;

    /**
     * Execute PI trades. We know the winners and losers so price is not an issue.
     */
    public function executeFundraisingTrade($trade_url, $yes_or_no)
    {
        return;
        die();
        $this->makePiDriver('TraderBot');
        Cache::put('TraderBot', $this->driver->getSessionID(), 30);

        $this->helperGetAUrl($trade_url, 'data1');
        usleep(200000);

        $shares = $this->helperGetMyShares();
        $prices = $this->helperGetOpenMarketOffers();

        // d($shares, $prices);
        if ($prices) {
            if ($this->needsLiquidation($yes_or_no, $shares)) {
                $trade_shares = $shares['myShares'];
                $trade_price = $yes_or_no == 'Yes' ? 100-$this->max_no_price : 100-$this->max_yes_price;
                $new_yes_or_no = $yes_or_no == 'Yes' ? 'No' : 'Yes';
                $this->placeTrade($trade_url, 'sell', $new_yes_or_no, $trade_shares, $trade_price);
            } else {
                $trade_shares = $yes_or_no == 'Yes' ? $this->max_yes_shares : $this->max_no_shares;
                $trade_price = $yes_or_no == 'Yes' ? $this->max_yes_price : $this->max_no_price;
                $this->placeTrade($trade_url, 'buy', $yes_or_no, $trade_shares, $trade_price);
            }
        }

        if ($this->localDom) {
            $this->localDom->clear();
            unset($this->localDom);
        }

        Cache::put('TraderBot', $this->driver->getSessionID(), 30);
    }

    /**
     * Check your owned shares. Do you need to liquidate or buy more?
     */
    protected function needsLiquidation($yes_or_no, $shares)
    {
        // Make sure to cancel all your offers before running this.
        if (isset($shares['myOffers'])) {
            // die('cancel all your orders, dummy');
        }
        // If no owned shares AND no outstanding offers, no liquidation
        elseif (!isset($shares['type'])) {
            return false;
        }
        // If we already have the same type of shares, no liquidation
        elseif ($shares['type'] == $yes_or_no) {
            return false;
        }
        // If we own the opposite type of shares, return that we need to sell them.
        else {
            return true;
        }
    }

    protected function helperGetMyShares()
    {
        $shares = array();
        $dashboard = $this->helperGetAnElement('div[class=dashboard]', 0);
        foreach ($dashboard->find('p') as $stat) {
            $text = $stat->plaintext;
            if (stristr($text, 'Your Shares')) {
                $shares['myShares'] = trim($stat->find('a', 0)->plaintext);
                $shares['type'] = stristr($stat->find('a', 0), 'alert-danger') ? 'No' : 'Yes';
            }
            elseif (stristr($text, 'Your Offers')) {
                foreach ($stat->find('span') as $span) {
                    if ( ! stristr($span->plaintext, 'Your Offers')) {
                        $offerType = $span->find('b', 0)->plaintext;
                        $shares['myOffers'][$offerType] = trim($span->find('a b', 0)->plaintext);
                    }
                }
            }
        }
        return $shares;
    }

    protected function helperGetOpenMarketOffers()
    {
        $openOffers = array();
        $yesOffers = $this->helperGetAnElement('div[id=openoffers1] table', 0);
        foreach ($yesOffers->find('tbody tr') as $tableRow) {
            $isNotHeader = $tableRow->find('td', 0);
            if ($isNotHeader) {
                $buyOffer = array();
                $buyOffer['price'] = (int) $tableRow->find('td', 0)->plaintext;
                $buyOffer['shares'] = (int) $tableRow->find('td', 1)->plaintext;
                if ($buyOffer['price'] && $buyOffer['price'] != 'Price' && ! stristr($tableRow->find('td', 0), 'info')) {
                    $openOffers['buyYes'][] = $buyOffer;
                }

                $sellOffer = array();
                $sellOffer['price'] = (int) $tableRow->find('td', 3)->plaintext;
                $sellOffer['shares'] = (int) $tableRow->find('td', 4)->plaintext;
                if ($sellOffer['price'] && $sellOffer['price'] != 'Price' && ! stristr($tableRow->find('td', 3), 'info')) {
                    $openOffers['sellYes'][] = $sellOffer;
                }
            }
        }

        return $openOffers;
    }
}

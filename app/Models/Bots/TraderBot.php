<?php

use Sunra\PhpSimple\HtmlDomParser;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverDimension;

define('DEFAULT_SHARES_IN_BLOCKING_BID', 40);
define('DEFAULT_MAX_SHARES_TO_BUY', 400);
define('DEFAULT_MIN_SHARES_TO_RETAIN', 0);
define('DEFAULT_MAX_SHARES_OPEN_ORDERS', 150);
define('DEFAULT_SHARES_PER_ORDER', 50);
define('DEFAULT_CHURN_RANGE', 2);
define('DEFAULT_PRICE_SPREAD', 9);
define('MIN_SHARES_TO_DISPATCH_NEW_AUTOTRADE', 5);

class TraderBot extends Bot
{
    public $beast_mode = false;
    public $m1 = 1; // Weight of my estimate
    public $m2 = 1; // Weight of market's opinion

    public $localDom = false;

    protected function makePiDriver($session_name)
    {
        $this->makeDriver('https://www.predictit.org/', $session_name);
        if ($this->driver->getCurrentURL() == 'https://www.predictit.org/') {
            if ($this->PIisOffline()) {
                $this->driver->get('https://www.predictit.org/');
                sleep(mt_rand(1,4));
                if ($this->PIisOffline()) {
                    return false;
                }
            }
            $this->piLogin();
        }
        return true;
    }

    private function PIisOffline()
    {
        $html = $this->driver->getPageSource();
        if (strlen($html) < 5000 || stristr($html, 'Closed For Maintenance')) {
            return true;
        }
        return false;
    }
    
    /**
     * Using the parameters on $dt (which is NOT a PiQuestion), place a trade.
     * Can't scrape since the related PiQuestion uses a different series of PiContest id's.
     * Just get the URL, the Yes/No values, and the price/shares, and execute it.
     */
    public function executeRcpDropTrade(RcpDropTrade $dt)
    {
        if ($dt->active && $dt->auto_trade_me) {
            $this->makePiDriver('TraderBot');
            Cache::put('TraderBot', $this->driver->getSessionID(), 30);

            $this->helperGetAUrl($dt->pi_question->url_of_market, 'data1');
            usleep(200000);
            $this->placeTrade($dt->pi_question->url_of_market, $dt->buy_or_sell, $dt->yes_or_no, $dt->shares, $dt->price);

            Cache::put('TraderBot', $this->driver->getSessionID(), 30);
        }
    }

    public function executeRcpAddTrade(RcpAddTrade $dt)
    {
        if ($dt->active && $dt->auto_trade_me) {
            $this->makePiDriver('TraderBot');
            Cache::put('TraderBot', $this->driver->getSessionID(), 30);

            $this->helperGetAUrl($dt->pi_question->url_of_market, 'data1');
            usleep(200000);
            $this->placeTrade($dt->pi_question->url_of_market, $dt->buy_or_sell, $dt->yes_or_no, $dt->shares, $dt->price);

            Cache::put('TraderBot', $this->driver->getSessionID(), 30);
        }
    }

    /**
     * Make sure we have a Selenium session ready and waiting. Reload my account balance so I don't have to - timesaver!
     */
    public function keepTraderBotWarm($return_source_code = false)
    {
        // Cache::forget('TraderBot');
        if (!Cache::has('trade_in_progress')) {
            if ($this->makePiDriver('TraderBot')) {
                Cache::put('TraderBot', $this->driver->getSessionID(), 30);
                $this->helperGetAUrl('https://www.predictit.org/Profile/History', 'divTblPaginate');
                usleep(200000);
                Cache::put('TraderBot', $this->driver->getSessionID(), 30);
                if ($return_source_code) {
                    return $this->driver->getPageSource();
                }
            }
        }
    }

    /**
     * Get the source code of account history page. Parse it for trade records.
     * To determine what's new, compare trade execution times to the oldest we have in the DB.
     * Dispatch a new AutoTrade, with a flag, if the trade was of at least a certain size of shares.
     */
    public function checkTrades()
    {
        // Get oldest executed autotrade.
        $oldest = ExecutedAutotrade::orderBy('trade_executed_datetime', 'desc')->first();
        d($oldest);
        die();
        // Compare each trade time to the oldest one, so that we know if this one matters.
        // Create a new trade record even if the number of shares is below the dispatch threshold.
        // Actually make the trade dispatch.
        // Include a flag? Opposite action only? Clear a flag? Make next buy 1 cent cheaper?
        // Include a flag - perform the opposite action as normal, and the repeat action 1 cent cheaper.
        // Also clear any flags on the related contract that were incrementing to show inactivity.

        $dom = HtmlDomParser::str_get_html($this->keepTraderBotWarm(true));
        $trades_to_dispatch = array();

        $table = $dom->find('table[id=tablesorter] tbody[id=tbHistory]', 0);
        $header = true;
        foreach ($table->find('tr') as $trade) {
            if ($header) {
                $header = false;
            }
            else {
                $shares = $trade->find('td', 3)->plaintext;
                $time = date('Y-m-d H:i:s', strtotime($trade->find('td', 0)->plaintext));
                if ($shares > MIN_SHARES_TO_DISPATCH_NEW_AUTOTRADE) {
                    $ticker = $trade->find('td', 2)->plaintext;
                    $question = PiQuestion::where('question_ticker', '=', $ticker)->first();
                    if ($question && $question->auto_trade_me) {
                        if ( ! $question->pi_contest ||
                            ($question->pi_contest && $question->pi_contest->auto_trade_this_contest)
                            ) {
                            if ( ! in_array($question, $trades_to_dispatch)) {
                                $trades_to_dispatch[] = $question;
                            }
                            $exec = new ExecutedAutotrade();
                            $exec->trade_executed_datetime = $time;
                            $exec->action = $trade->find('td', 1)->plaintext;

                            $exec->question_ticker = $ticker;
                            $exec->pi_question_id = $question->id;

                            $exec->shares = $trade->find('td', 3)->plaintext;
                            $exec->price = $trade->find('td', 4)->plaintext;
                            $exec->profit = $this->formatPiCurrency($trade->find('td', 5)->plaintext);
                            $exec->fees = $this->formatPiCurrency($trade->find('td', 6)->plaintext);
                            $exec->risk_adjustment = $this->formatPiCurrency($trade->find('td', 7)->plaintext);
                            $exec->credit = $this->formatPiCurrency($trade->find('td', 8)->plaintext);
                            d($trade->plaintext, $exec);
                            
                            $exec->save();
                        }
                    }
                }
                
            }
        }

        $dom->clear();
        unset($dom);
    }

    protected function formatPiCurrency($value)
    {
        $negative = stristr($value, '(') ? '-' : '';
        $value = substr($value, stripos($value, '$') + 1);
        $value = str_replace(')', '', $value);
        $value = str_replace(' ', '', $value);
        $value = $negative . $value;
        return $value;
    }

    /**
     * Get a PiQuestion and url_of_market. Visit with Selenium. 
     * Put the Selenium session into the cache for later.
     * Place trades if parameter given.
     * @todo check that the contest isn't past its close date.
     */
    public function visitQuestionMarket(PiQuestion $question, $place_trades = false, $cancel_orders = false)
    {
        if ($this->makePiDriver('QuestionVisitBot')) {
            Cache::put('QuestionVisitBot', $this->driver->getSessionID(), 30);
            $this->handleTrading($question, $place_trades, $cancel_orders);
        }
    }

    public function visitContestMarket(PiContest $contest, $place_trades = false, $cancel_orders = false)
    {
        if ($this->makePiDriver('QuestionVisitBot')) {
            Cache::put('QuestionVisitBot', $this->driver->getSessionID(), 30);
            $url = str_replace("&#39;", "'", $contest->url_of_answer);
            $this->driver->get($url);
        }
    }

    public function autoTrade()
    {
        $this->makePiDriver('TraderBot');

        $trades = PiQuestion::where('auto_trade_me', '=', 1)->where('active', '=', 1)->get();
        foreach ($trades as $trade) {
            $this->handleTrading($trade, true);
            usleep(200000);
        }
        
        Cache::put('TraderBot', $this->driver->getSessionID(), 15);
    }

    /**
     * Market data gets saved whether we are trading or not.
     */
    // @todo: figure out when the market has gotten too congested and my orders are stale, so cancel them.
    // @todo: Make Beast Mode able to sell owned shares as well as buy new ones.
    // @todo: Beast Mode goes off some values already saved on the model.
    // @todo: Beast Mode assumes that we have no positions and are going to buy the maximum shares, as set in admin config.
    protected function handleTrading($trade, $place_trades, $cancel_orders)
    {
        $this->helperGetAUrl($trade->url_of_market, 'data1');
        usleep(200000);

        if ($cancel_orders) {
            $this->helperCancelAllOpenOrders();
        }

        $shares = $this->helperGetShares($trade);
        $prices = $this->helperGetPrices($trade);

        if ($place_trades && $prices) {
            $this->prepareForTrade($trade, $shares, $prices);
            $this->handleOrderPlacement('buy', $trade, $shares, $prices);
            if ( ! $this->beast_mode) {
                $this->handleOrderPlacement('sell', $trade, $shares, $prices);
            }
        }

        if ($this->localDom) {
            $this->localDom->clear();
            unset($this->localDom);
        }
    }

    // @todo what if the market's total values don't add up to 100?
    // @todo should I be re-calculating market value based on things having to add up to 100?
    // is that relevant? what about the "other" option???
    protected function prepareForTrade($trade, $shares, $prices)
    {
        $yes_value = (($trade->chance_to_win * $this->m1) + ($trade->cache_market_support_yes_side_price * $this->m2)) / ($this->m1 + $this->m2);
        $no_value = (((100 - $trade->chance_to_win) * $this->m1) + ($trade->cache_market_support_no_side_price * $this->m2)) / ($this->m1 + $this->m2);
        $yes_discount = $yes_value - $trade->cache_market_support_yes_side_price;
        $no_discount = $no_value - $trade->cache_market_support_no_side_price;

        if (isset($shares['type'])) {
            $trade->yes_or_no = $shares['type'];
        }
        elseif (isset($shares['myOffers'])) {
            $trade->yes_or_no = isset($shares['myOffers']['Buy Yes']) ? 'Yes' : 'No';
        }
        elseif ($trade->category != 'default') {
            $trade->yes_or_no = $trade->category;
        }
        elseif (isset($prices['buyYes'][0]['price'])) {
            $trade->yes_or_no = $yes_discount > $no_discount ? 'Yes' : 'No';
        } 
        else {
            $trade->yes_or_no = 'No';
        }
        $trade->save();
    }

    /**
     * Validate 3 ways before placing a trade.
     * 1- Within share limits.
     * 2- Auto-trade has been set to ON.
     * 3- The entire contest's Competition Total == 100.
     * Place trades in 'beast mode' i.e., active buying instead of passive accumulation, if applicable.
     */
    protected function handleOrderPlacement($buy_or_sell, $trade, $shares, $prices)
    {
        $tradeShares = $this->helperChooseShares($buy_or_sell, $trade, $shares);
        if ($tradeShares > 0 && $this->checkReadyToTrade($trade)) {
            $tradePrice = $this->helperChooseBestPrice($buy_or_sell, $trade, $prices);
            if ($tradePrice > 0 && $tradePrice < 100) {
                $this->placeTrade($trade->url_of_market, $buy_or_sell, $trade->yes_or_no, $tradeShares, $tradePrice);
            }
        }
    }

    protected function checkReadyToTrade(PiQuestion $trade)
    {
        if ($trade->auto_trade_me &&
            $trade->pi_contest->competition_total >= 95 &&
            $trade->pi_contest->competition_total <= 105) 
        {
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

    protected function helperGetAnElement($hint, $iteration, $check_once = false)
    {
        for ($i=0; $i < 7; $i++) { 
            if ($this->localDom) {
                $this->localDom->clear();
                unset($this->localDom);
            }

            $this->localDom = HtmlDomParser::str_get_html($this->driver->getPageSource());
            if ( ! $this->localDom) {
                echo $i . "--Dom is missing! " . PHP_EOL;
                usleep(200000);
            }
            else {
                $thing = $this->localDom->find($hint, $iteration);
                if ($thing) {
                    return $thing;
                }
                else {
                    echo $i . "--Thing is missing! " . $hint . PHP_EOL;
                    usleep(200000);
                    if ($check_once) {
                        break;
                    }
                }
            }
        }
        return false;
    }

    protected function helperGetShares(PiQuestion $question)
    {
        $shares = array();
        $dashboard = $this->helperGetAnElement('div[class=dashboard]', 0);
        foreach ($dashboard->find('p') as $stat) {
            $text = $stat->plaintext;
            if (stristr($text, 'Your Shares')) {
                $shares['myShares'] = trim($stat->find('a', 0)->plaintext);
                $shares['type'] = stristr($stat->find('a', 0), 'alert-danger') ? 'No' : 'Yes';
                $question->cache_current_shares = $shares['myShares'];
                $question->cache_current_position_is_yes = $shares['type'] == 'Yes' ? true : false;
                $question->save();
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

    protected function helperGetPrices(PiQuestion $question)
    {
        $scrape = new Scrape();
        $scrape->save();
        $market = new PiMarket();
        $market->pi_question_id = $question->id;
        $market->pi_contest_id = $question->pi_contest_id;
        $market->scrape_id = $scrape->id;

        $lastPrice = $this->helperGetAnElement('div[class=dashboard] p strong', 0);
        if ($lastPrice) {
            $lastPrice = str_replace('Latest Price: ', '', $lastPrice->plaintext);
        }
        $market->last_price = (int) $lastPrice;
        $question->cache_last_trade_price = (int) $lastPrice;

        $topline = $this->helperGetAnElement('div[id=data1] table', 0);
        $this->helperGetPricesHandleTopline($topline, $market, $question);

        $openOffers = array();
        $yesOffers = $this->helperGetAnElement('div[id=openoffers1] table', 0);
        $this->helperGetPricesHandleYesOffers($openOffers, $yesOffers, $market, $question);

        $market->save();
        $question->save();
        return $openOffers;
    }

    protected function helperGetPricesHandleYesOffers(&$openOffers, $yesOffers, $market, $question)
    {
        foreach ($yesOffers->find('tbody tr') as $tableRow) {
            $market->extractYesOffers($tableRow);
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
        $values = $market->marketValues('values', false); // False is needed to return without money format
        $question->cache_market_support_yes_side_price = $values['yesPrice'];
        $question->cache_market_support_no_side_price = $values['noPrice'];
        $question->cache_market_support_yes_side_dollars = $values['yesValue'];
        $question->cache_market_support_no_side_dollars = $values['noValue'];
        $question->cache_market_support_net_price_spread = $values['totalPrice'];
        $question->cache_market_support_net_dollars = $values['netYes'];
        $question->cache_market_support_ratio_price = $values['ratioPrice'];
        $question->cache_market_support_ratio_dollars = $values['ratioValue'];
        $question->save();
    }

    protected function helperGetPricesHandleTopline($topline, $market, $question)
    {
        $market->shares_traded = str_replace(',', '', $topline->find('tr', 4)->find('td', 1)->plaintext);
        $market->todays_volume = str_replace(',', '', $topline->find('tr', 5)->find('td', 1)->plaintext);
        $market->total_shares = str_replace(',', '', $topline->find('tr', 6)->find('td', 1)->plaintext);
        $market->save();

        $question->cache_todays_volume = str_replace(',', '', $topline->find('tr', 5)->find('td', 1)->plaintext);
        $question->cache_total_shares = str_replace(',', '', $topline->find('tr', 6)->find('td', 1)->plaintext);
        $question->save();
    }

    protected function placeTrade($market_url, $buy_or_sell, $yes_or_no, $shares, $price)
    {
        $this->helperClickFirstTradeButton($market_url, $buy_or_sell, $yes_or_no);
        $this->helperSendSharesPriceAndSubmit($buy_or_sell, $yes_or_no, $shares, $price);
        $this->helperConfirmFormAndDismiss();
    }

    protected function helperConfirmFormAndDismiss()
    {
        // Adding a pause to hopefully avoid the NoSuchElementException from the next Wait for visibility
        // Might need a function that can try/catch.
        usleep(200000);
        // Wait until the submit confirmation modal is there. Form ID seems is the same in all cases?!?
        $form_id = 'BuyTradeSubmit';
        $this->driver->wait()->until(
          WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id($form_id))
        );
        usleep(200000);
        $this->driver->findElement(WebDriverBy::id($form_id))->submit();

        // Wait until the order confirmation modal is there.
        $this->driver->wait()->until(
          WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('showHistory'))
        );
        usleep(200000);
        $this->driver->findElement(WebDriverBy::id('closeModal'))->click();

        // Wait until everything else is finished before passing it back, to not lose navigation.
        $this->driver->wait()->until(
          WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::id('showHistory'))
        );
        // $this->driver->wait()->until(
        //   WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('avail'))
        // );
        sleep(2);
    }

    protected function helperSendSharesPriceAndSubmit($buy_or_sell, $yes_or_no, $shares, $price)
    {
        // Adding a pause to hopefully avoid the NoSuchElementException from the next Wait for visibility
        usleep(150000);
        // Wait until the modal opens and the fields are available to enter data into.
        $this->driver->wait()->until(
          WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('Quantity'))
        );
        usleep(150000);
        $this->driver->findElement(WebDriverBy::id('Quantity'))->clear();
        usleep(150000);
        $this->driver->findElement(WebDriverBy::id('Quantity'))->sendKeys($shares);
        usleep(150000);

        // Clear existing price from field before typing new price.
        $this->driver->findElement(WebDriverBy::id('PricePerShare'))->clear();
        usleep(150000);
        $this->driver->findElement(WebDriverBy::id('PricePerShare'))->sendKeys($price);
        usleep(150000);

        // Buying a Yes is called submitBuy. Selling a Yes is called submitSell.
        // Buying OR Selling a No is called submitSell.
        $button_id = ($buy_or_sell == 'buy' && $yes_or_no == 'Yes') ? 'submitBuy' : 'submitSell';
        $this->driver->findElement(WebDriverBy::id($button_id))->click();
    }

    // when you own nothing, buttons are called simpleYes and simpleNo
    // when you own yes, they are called simpleYes (buy) and sellYes-####
    // when you own no, they are called simpleNo (buy) and sellNo-####
    protected function helperClickFirstTradeButton($market_url, $buy_or_sell, $yes_or_no)
    {
        usleep(150000);
        $market_id = substr($market_url, strpos($market_url, '/', strpos($market_url, 'Contract')) + 1);
        $market_id = substr($market_id, 0, strpos($market_id, '/'));
        $button_id = $buy_or_sell == 'buy' ? 'simple' . $yes_or_no : 'sell' . $yes_or_no . '-' . $market_id;

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id($button_id)));
        $this->driver->findElement(WebDriverBy::id($button_id))->click();
    }

    /**
     * First go to the ownership tab.
     * Could have zero, one, or multiple open orders.
     * Find the right button to click, then wait for the alert and accept it.
     */
    protected function helperCancelAllOpenOrders()
    {
        $dashboard = $this->helperGetAnElement('div[class=dashboard]', 0);
        foreach ($dashboard->find('p') as $stat) {
            $text = $stat->plaintext;
            // Are there open orders?
            if (stristr($text, 'Your Offers')) {
                // Click the tab and open the panel.
                $this->driver->wait()->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('getOwnership')));
                $this->driver->findElement(WebDriverBy::id('getOwnership'))->click();
                $this->driver->wait()->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath('//a[@href="#myOffers1"]')));
                usleep(200000);
                $this->driver->findElement(WebDriverBy::xpath('//a[@href="#myOffers1"]'))->click();
                usleep(200000);

                // Look for the cancel buttons. Click and then dismiss the alert.
                $cancel_all_button = $this->helperGetAnElement('a[id=cancelAllOffers]', 0, true);
                if ($cancel_all_button) {
                    $this->driver->findElement(WebDriverBy::id('cancelAllOffers'))->click();
                }
                else {
                    $this->driver->findElement(WebDriverBy::className('cancelOrderBook'))->click();
                }
                $this->driver->wait()->until(WebDriverExpectedCondition::alertIsPresent());
                usleep(200000);
                $this->driver->switchTo()->alert()->accept();

                // Before returning, use the same wait that we use after placing a trade.
                $this->driver->wait()->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('getData')));
                usleep(200000);
            }
        }

        return;
    }

    protected function piLogin()
    {
        $dim = new WebDriverDimension(1200, 850);
        $this->driver->manage()->window()->setSize($dim);

        // Make sure the button to open the signin modal exists.
        $this->driver->wait()->until(
          WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(WebDriverBy::xpath('//a[@href="#SignInModal"]'))
        );
        $this->driver->findElement(WebDriverBy::xpath('//a[@href="#SignInModal"]'))->click();

        // Wait until the modal opens and the fields are available to enter data into.
        $this->driver->wait()->until(
          WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('Email'))
        );
        usleep(200000);
        $this->driver->findElement(WebDriverBy::id('Email'))->sendKeys('robertcharlesfox@gmail.com');
        usleep(200000);
        $this->driver->findElement(WebDriverBy::id('Password'))->sendKeys(env('PI_PW'));
        usleep(200000);

        $this->driver->findElement(WebDriverBy::id('loginForm'))->submit();

        // Wait until the authenticated response page loads up.
        $this->driver->wait()->until(
          WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('shares'))
        );

        $dim = new WebDriverDimension(800, 850);
        $this->driver->manage()->window()->setSize($dim);
    }
}

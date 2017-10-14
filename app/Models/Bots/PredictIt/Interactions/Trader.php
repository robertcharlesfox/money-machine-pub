<?php namespace PredictIt;

use Sunra\PhpSimple\HtmlDomParser;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverException;
use UnknownServerException;
use Cache;
use PiContest;
use Log;

class Trader extends Base
{
    private $max_sleep = 2; // seconds
    private $long_sleep = 250000; // milliseconds
    private $short_sleep = 70000; // milliseconds
    private $mini_sleep = 20000; // milliseconds
    private $driver;

    public function nazgulPrepareCancelOrders($driver)
    {
        $this->helperCancelAllOpenOrders($driver, true, false, false);
    }

    public function nazgulReleaseCancelOrders($driver)
    {
        $this->helperCancelAllOpenOrders($driver, false, true, false);
    }

    public function nazgulPrepareTrade($nazgul, $driver)
    {
        if (!$this->checkForKillSwitch()) {
            return;
        }
        $this->driver = $driver;
        $this->helperClickFirstTradeButton($nazgul->pi_question->url_of_market, $nazgul->buy_or_sell, $nazgul->yes_or_no);
    }

    public function nazgulReleaseTrade($nazgul, $driver)
    {
        if (!$this->checkForKillSwitch()) {
            return;
        }
        $this->driver = $driver;

        $price = $nazgul->price_limit;
        $shares = $nazgul->buy_or_sell == 'buy' ? (int) ($nazgul->risk / ($nazgul->price_limit / 100)) : $nazgul->risk;
        $this->helperSendSharesPriceAndSubmit($nazgul->buy_or_sell, $nazgul->yes_or_no, $shares, $price);
        $this->helperConfirmFormAndDismiss(false, $nazgul->buy_or_sell);
    }

    public function placeTrade($buy_or_sell, $yes_or_no, $shares, $price, $market_url, $driver)
    {
        if (!$this->checkForKillSwitch()) {
            return;
        }
        $this->driver = $driver;
        $this->helperClickFirstTradeButton($market_url, $buy_or_sell, $yes_or_no);
        $this->helperSendSharesPriceAndSubmit($buy_or_sell, $yes_or_no, $shares, $price);
        $this->helperConfirmFormAndDismiss();
    }

    public function setDriver($driver)
    {
        $this->driver = $driver;
    }

    public function placeCompetitionTrade($buy_or_sell, $yes_or_no, $shares, $price, $market_id)
    {
        if (!$this->checkForKillSwitch()) {
            return;
        }
        $this->helperClickCompetitionTradeButton($buy_or_sell, $yes_or_no, $market_id);
        $this->helperSendSharesPriceAndSubmit($buy_or_sell, $yes_or_no, $shares, $price);
        $this->helperConfirmFormAndDismiss();
    }

    /**
     * This is the "master kill-switch" for all auto-trades.
     * Check for a value in the database. If it isn't there, ABORT!
     * If it is there, put a value into Cache so that the Warmup program doesn't collide.
     */
    protected function checkForKillSwitch()
    {
        // This is just a dummy record to use for saving data.
        $autotrade = PiContest::find(186);
        if ($autotrade->auto_trade_this_contest) {
            Cache::put('trade_in_progress', 'yes', 2);
            return true;
        }
        Log::info('aborted a trade bc of killswitch!!');
        echo 'aborted a trade bc of killswitch!!';
        return false;
    }

    protected function helperConfirmFormAndDismiss($dismiss = true, $buy_or_sell = 'buy')
    {
        // Adding a pause to hopefully avoid the NoSuchElementException from the next Wait for visibility
        // Might need a function that can try/catch.
        usleep($this->long_sleep);
        // Wait until the submit confirmation modal is there. Form ID seems is the same in all cases?!?
        $form_id = 'BuyTradeSubmit';
        // $form_id = $buy_or_sell == 'buy' ? 'BuyTradeSubmit' : 'SellSubmit';
        $this->driver->wait()->until(
          WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id($form_id))
        );
        usleep($this->long_sleep);
        $this->driver->findElement(WebDriverBy::id($form_id))->submit();

        if ($dismiss) {
            // Wait until the order confirmation modal is there.
            $this->driver->wait()->until(
              WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('showHistory'))
            );
            usleep($this->long_sleep);
            $this->driver->findElement(WebDriverBy::id('closeModal'))->click();

            // Wait until everything else is finished before passing it back, to not lose navigation.
            $this->driver->wait()->until(
              WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::id('showHistory'))
            );

            // This is probably the most essential step. Wait for the interference object to go away.
            $this->driver->wait()
                ->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::id('spinnnerGo')));
        }
    }

    protected function helperSendSharesPriceAndSubmit($buy_or_sell, $yes_or_no, $shares, $price)
    {
        // Adding a pause to hopefully avoid the NoSuchElementException from the next Wait for visibility
        usleep($this->short_sleep);
        // Wait until the modal opens and the fields are available to enter data into.
        $this->driver->wait()->until(
          WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('Quantity'))
        );
        usleep($this->short_sleep);
        $this->driver->findElement(WebDriverBy::id('Quantity'))->clear();
        usleep($this->short_sleep);
        $this->driver->findElement(WebDriverBy::id('Quantity'))->sendKeys($shares);
        usleep($this->short_sleep);

        // Clear existing price from field before typing new price.
        $this->driver->findElement(WebDriverBy::id('PricePerShare'))->clear();
        usleep($this->short_sleep);
        $this->driver->findElement(WebDriverBy::id('PricePerShare'))->sendKeys($price);
        usleep($this->short_sleep);

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
        // This is probably the most essential step. Make sure we aren't waiting for old order to finish.
        $this->driver->wait()
            ->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::id('spinnnerGo')));

        usleep($this->short_sleep);
        $market_id = substr($market_url, strpos($market_url, '/', strpos($market_url, 'Contract')) + 1);
        $market_id = substr($market_id, 0, strpos($market_id, '/'));
        $button_id = $buy_or_sell == 'buy' ? 'simple' . $yes_or_no : 'sell' . $yes_or_no . '-' . $market_id;

        $this->driver->wait()->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id($button_id)));
        $this->driver->findElement(WebDriverBy::id($button_id))->click();
    }

    // buttons have id of buyYes-#### and buyNo-####
    protected function helperClickCompetitionTradeButton($buy_or_sell, $yes_or_no, $market_id)
    {
        // This is probably the most essential step. Make sure we aren't waiting for old order to finish.
        $this->driver->wait()
            ->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::id('spinnnerGo')));

        $button_id = $buy_or_sell . $yes_or_no . '-' . $market_id;
        usleep($this->mini_sleep);
        $this->driver->wait()
            ->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id($button_id)));
        usleep($this->mini_sleep);
        $this->driver->wait()
            ->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id($button_id)));
        usleep($this->mini_sleep);
        $this->driver->wait()
            ->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id($button_id)));
        usleep($this->mini_sleep);
        $this->driver->wait($this->max_sleep, $this->mini_sleep)
            ->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id($button_id)));
        $this->driver->findElement(WebDriverBy::id($button_id))->click();
    }

    /**
     * First go to the ownership tab.
     * Could have zero, one, or multiple open orders.
     * Find the right button to click, then wait for the alert and accept it.
     * Params default to making full process happen. 
     * If only trying to execute portions (via Nazgul, probably), the params matter.
     */
    public function helperCancelAllOpenOrders($driver, $initiate = true, $proceed = true, $wait = true)
    {
        $this->driver = $driver;

        if ($initiate) {
            // Click the tab and open the panel.
            $this->driver->wait()->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('getOwnership')));
            $this->driver->findElement(WebDriverBy::id('getOwnership'))->click();
            $this->driver->wait()->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath('//a[@href="#myOffers1"]')));
            usleep($this->short_sleep);
            $this->driver->findElement(WebDriverBy::xpath('//a[@href="#myOffers1"]'))->click();
            usleep($this->short_sleep);
        }

        if ($proceed) {
            // Look for the cancel buttons. Click and then dismiss the alert.
            $dom = HtmlDomParser::str_get_html($this->driver->getPageSource());
            $cancel_all_button = $dom->find('a[id=cancelAllOffers]', 0);
            if ($cancel_all_button) {
                $this->driver->findElement(WebDriverBy::id('cancelAllOffers'))->click();
            }
            else {
                $this->driver->findElement(WebDriverBy::className('cancelOrderBook'))->click();
            }
            $this->driver->wait()->until(WebDriverExpectedCondition::alertIsPresent());
            usleep($this->short_sleep);
            $this->driver->switchTo()->alert()->accept();
        }

        if ($wait) {
            // This is probably the most essential step to ensure you return a usable page.
            $this->driver->wait()
                ->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::id('spinnnerGo')));

            // Before returning, use the same wait that we use after placing a trade.
            $this->driver->wait()->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('getData')));
            usleep($this->short_sleep);
        }
        return;
    }

    /**
     * Cancel orders from the competition page.
     * Find the button through xpath property.
     * Find the right button to click, then wait for the alert and accept it.
     */
    public function cancelCompetitionOrders($outcome_id)
    {
        // Click the button.
        $xpath_expression = '//a[@onclick="openOwnership(' . $outcome_id . ", 'offered')" . '"]';
        $this->driver->wait($this->max_sleep, $this->short_sleep)
            ->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath($xpath_expression)));
        $this->driver->findElement(WebDriverBy::xpath($xpath_expression))->click();

        // Wait until the modal opens.
        $this->driver->wait()
            ->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('myOffers1')));

        // Look for the cancel buttons. Click and then dismiss the alert.
        $dom = HtmlDomParser::str_get_html($this->driver->getPageSource());
        $cancel_all_button = $dom->find('a[id=cancelAllOffers]', 0);
        if ($cancel_all_button) {
            $this->driver->findElement(WebDriverBy::id('cancelAllOffers'))->click();
        } else {
            $this->driver->findElement(WebDriverBy::className('cancelOrderBook'))->click();
        }
        $this->driver->wait()
            ->until(WebDriverExpectedCondition::alertIsPresent());
        $this->driver->switchTo()->alert()->accept();

        // Wait for the final modal and then close it.
        // Lots of seemingly redundant steps here, but it makes sure that elements refresh.
        usleep($this->long_sleep);
        $this->driver->wait()
            ->until(WebDriverExpectedCondition::refreshed(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('cancelModal'))));
        usleep($this->long_sleep);
        $this->driver->wait()
            ->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('cancelModal')));
        usleep($this->long_sleep);
        $this->driver->wait()
            ->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('cancelModal')));
        usleep($this->long_sleep);
        $this->driver->wait($this->max_sleep, $this->short_sleep)
            ->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('cancelModal')));

        // This is probably the most essential step.
        $this->driver->wait()
            ->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::id('spinnnerGo')));

        // Hopefully after all of these Waits, we finally have live elements.
        $this->driver->findElement(WebDriverBy::id('cancelModal'))->click();

        // Wait until everything else is finished before passing it back, to not lose navigation.
        $this->driver->wait()
            ->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::id('cancelModal')));
        $this->driver->wait($this->max_sleep, $this->short_sleep)
            ->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('getRules')));
        return;
    }
}

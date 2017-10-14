<?php namespace PredictIt;

use Sunra\PhpSimple\HtmlDomParser;
// use ExecutedAutotrade;
// define('MIN_SHARES_TO_DISPATCH_NEW_AUTOTRADE', 5);

use PiQuestion;
use PiContest;
use Scrape;
use PiMarket;
use Nazgul;

class Scraper extends Base
{
    public function logState(Nazgul $nazgul, $html)
    {
        $dom = HtmlDomParser::str_get_html($html);
        $dashboard = $dom->find('div[class=dashboard]', 0);
        $yesOffers = $dom->find('div[id=openoffers1] table', 0);
        $lastPrice = $dom->find('div[class=dashboard] p strong', 0);
        $topline = $dom->find('div[id=data1] table', 0);

        $shares = $this->helperGetShares($dashboard, $nazgul->pi_question);
        $prices = $this->helperGetPrices($lastPrice, $topline, $yesOffers, $nazgul->pi_question);
    }

    protected function helperGetShares($dashboard, PiQuestion $question)
    {
        $shares = array();
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

    protected function helperGetPrices($lastPrice, $topline, $yesOffers, PiQuestion $question)
    {
        $scrape = new Scrape();
        $scrape->save();
        $market = new PiMarket();
        $market->pi_question_id = $question->id;
        $market->pi_contest_id = $question->pi_contest_id;
        $market->scrape_id = $scrape->id;
        $market->offer_table = $yesOffers->outertext;

        if ($lastPrice) {
            $lastPrice = str_replace('Latest Price: ', '', $lastPrice->plaintext);
        }
        $market->last_price = (int) $lastPrice;
        $question->cache_last_trade_price = (int) $lastPrice;

        $this->helperGetPricesHandleTopline($topline, $market, $question);

        $openOffers = array();
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

    protected function formatPiCurrency($value)
    {
        $negative = stristr($value, '(') ? '-' : '';
        $value = substr($value, stripos($value, '$') + 1);
        $value = str_replace(')', '', $value);
        $value = str_replace(' ', '', $value);
        $value = $negative . $value;
        return $value;
    }

    public function getCompetitionPageData($page)
    {
        $shorter_page = substr($page, strpos($page, '<table id="contractListTable"'));
        $shorter_page = substr($shorter_page, 0, strpos($shorter_page, '/table>')+7);
        $data = [];

        $dom = HtmlDomParser::str_get_html($shorter_page);
        if (!$dom) {
            return false;
        }

        foreach ($dom->find('tbody tr') as $row) {
            // if ($row->find('td') && $row->class != 'hidden') {
            if ($row->find('td') && $row->id != 'showMoreLink') {
                $qdata = [];

                $questionLink = trim($row->find('div[class=outcome-title] a', 0)->href);
                $contract_id = substr($questionLink, 10);
                $contract_id = substr($contract_id, 0, strpos($contract_id, '/'));
                $qdata['contract']['url_of_market'] = 'https://www.predictit.org' . $questionLink;
                $qdata['contract']['contract_id'] = $contract_id;
                $qdata['contract']['question_ticker'] = $row->find('div[class=outcome-title] a p', 0)->plaintext;

                $buy_yes_cell = $row->find('td[class=text-center]', 1);
                $sell_yes_cell = $row->find('td[class=text-center]', 2);
                $qdata['prices']['buy_yes'] = (int) $buy_yes_cell->plaintext;
                $qdata['prices']['sell_yes'] = (int) $sell_yes_cell->plaintext;
                $buy_no_cell = $row->find('td[class=text-center]', 3);
                $sell_no_cell = $row->find('td[class=text-center]', 4);
                if (!$buy_yes_cell->find('a') && !$sell_yes_cell->find('a')) {
                    $qdata['my_shares']['yes_or_no'] = 'No';
                } elseif (!$buy_no_cell->find('a') && !$sell_no_cell->find('a')) {
                    $qdata['my_shares']['yes_or_no'] = 'Yes';
                } else {
                    $qdata['my_shares']['yes_or_no'] = '';
                }

                $qdata['my_shares']['quantity'] = $row->find('td[class=text-center]', 5)->plaintext;
                $qdata['my_shares']['buy_offers'] = $row->find('td[class=text-center]', 6)->plaintext;
                $qdata['my_shares']['sell_offers'] = $row->find('td[class=text-center]', 7)->plaintext;
                
                $data[$contract_id] = $qdata;
            }
        }

        $dom->clear();
        unset($dom);
        return $data;
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
}

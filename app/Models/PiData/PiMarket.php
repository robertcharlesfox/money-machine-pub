<?php

use Sunra\PhpSimple\HtmlDomParser;

// Number of consecutive times the bid/ask offer table has to be missing before we give up.
define('LIMIT_MISSING_SCRAPE_TABLE', 3);

class PiMarket extends Eloquent {

    public function pi_contest()
    {
        return $this->belongsTo(PiContest::class);
    }

    public function pi_question()
    {
        return $this->belongsTo(PiQuestion::class);
    }

    public function scrape()
    {
        return $this->belongsTo(Scrape::class);
    }

    public function pi_offers()
    {
        return $this->hasMany(PiOffer::class);
    }

    public function getTimeCreatedAttribute()
    {
        return date('m/d g:i a', strtotime($this->created_at));
    }

    public function scrapeQuestionMarket(PiQuestion $question, $scrape_id)
    {
        $this->pi_question_id = $question->id;
        $this->pi_contest_id = $question->pi_contest_id;
        $this->scrape_id = $scrape_id;
        $this->is_from_coordinated_scrape = 1;

        $operator = new BotPiOperatorNonAuth();
        $dom = $operator->getPiUrlNonAuth($question->url_of_market, 'openoffers1');
        if ($dom) {
            $last_price = $dom->find('div[class=dashboard] p strong', 0);
            if ($last_price) {
                $last_price = str_replace('Latest Price: ', '', $last_price->plaintext);
            }
            $this->last_price = (int) $last_price;
            $question->cache_last_trade_price = (int) $last_price;

            $topline = $dom->find('div[id=data1] table', 0);
            if ($topline) {
                if ($this->pi_contest_id == 163) {
                    $shares_traded = str_replace(',', '', $topline->find('tr', 3)->find('td', 1)->plaintext);
                    $todays_volume = str_replace(',', '', $topline->find('tr', 4)->find('td', 1)->plaintext);
                    $total_shares = str_replace(',', '', $topline->find('tr', 5)->find('td', 1)->plaintext);
                }
                else {
                    $shares_traded = str_replace(',', '', $topline->find('tr', 4)->find('td', 1)->plaintext);
                    $todays_volume = str_replace(',', '', $topline->find('tr', 5)->find('td', 1)->plaintext);
                    $total_shares = str_replace(',', '', $topline->find('tr', 6)->find('td', 1)->plaintext);
                }
                $question->cache_todays_volume = $todays_volume;
                $question->cache_total_shares = $total_shares;
                $this->shares_traded = $shares_traded;
                $this->todays_volume = $todays_volume;
                $this->total_shares = $total_shares;
            }

            $question->save();
            $this->save();

            // We know the openoffers1 object exists, but if there is no price table, it's expired.
            $yesOffers = $dom->find('div[id=openoffers1] table', 0);
            if ($yesOffers) {
                $this->offer_table = $yesOffers->outertext;
                $this->save();

                $question->count_missing_table = 0;

                foreach ($yesOffers->find('tbody tr') as $tableRow) {
                    $this->extractYesOffers($tableRow);
                }

                $values = $this->marketValues('values', false); // False is needed to return without money format
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
            else {
                $question->count_missing_table++;
                Log::notice($question->count_missing_table . ' Offers table missing for ' . $question->question_ticker);
                if ($question->count_missing_table > LIMIT_MISSING_SCRAPE_TABLE) {
                    Log::notice('Offers table missing. Deactiving scrapes for ' . $question->question_ticker);
                    $question->auto_trade_me = 0;
                    $question->count_missing_table = 0;
                }
                $question->save();
            }

            $dom->clear();
            unset($dom);
        }
        else {
            Log::warning('Dom did not load. ' . $question->question_ticker);
        }
    }

    public function extractYesOffers($tableRow)
    {
        $isNotHeader = $tableRow->find('td', 0);
        if ($isNotHeader) {
            $buyOffer = new PiOffer();
            $buyOffer->pi_market_id = $this->id;
            $buyOffer->action = 'buyYes';
            $buyOffer->price = (int) $tableRow->find('td', 0)->plaintext;
            $buyOffer->shares = (int) $tableRow->find('td', 1)->plaintext;
            
            // Validate
            if ($buyOffer->price && $buyOffer->price != 'Price' && ! stristr($tableRow->find('td', 0), 'info')) {
                $buyOffer->save();
            }

            $sellOffer = new PiOffer();
            $sellOffer->pi_market_id = $this->id;
            $sellOffer->action = 'sellYes';
            $sellOffer->price = (int) $tableRow->find('td', 3)->plaintext;
            $sellOffer->shares = (int) $tableRow->find('td', 4)->plaintext;
            
            // Validate
            if ($sellOffer->price && $sellOffer->price != 'Price' && ! stristr($tableRow->find('td', 3), 'info')) {
                $sellOffer->save();
            }
        }
    }

    /**
     * Use a decrementing ratio of shares x price to see how much $$ support is really in a market.
     */
    public function marketValues($type = 'bids', $use_money_format = true)
    {
        setlocale(LC_MONETARY, 'en_US');
        $values = array();
        $buy_yes_offers = $this->pi_offers()->where('action', '=', 'sellYes')->get()->sortByDesc('price')->take(5);
        $yes_value = 0;
        $yes_shares = 1;
        $multiplier = 1.0;
        $multiplier_decrement = 0.15;
        foreach ($buy_yes_offers as $yes) {
            $yes_value += ($yes->price * $yes->shares) * $multiplier;
            $yes_shares += $yes->shares * $multiplier;
            $multiplier = $multiplier - $multiplier_decrement;
        }
        $buy_no_offers = $this->pi_offers()->where('action', '=', 'buyYes')->get()->sortBy('price')->take(5);
        $no_value = 0;
        $no_shares = 1;
        $multiplier = 1;
        foreach ($buy_no_offers as $no) {
            $no_value += ((100 - $no->price) * $no->shares) * $multiplier;
            $no_shares += $no->shares * $multiplier;
            $multiplier = $multiplier - $multiplier_decrement;
        }

        $values['yesValue'] = $use_money_format ? $this->formatDollars($yes_value / 100) : $yes_value / 100;
        $values['noValue'] = $use_money_format ? $this->formatDollars($no_value / 100) : $no_value / 100;
        $values['netYes'] = $use_money_format ? $this->formatDollars(($yes_value - $no_value) / 100) : ($yes_value - $no_value) / 100;
        $values['ratioValue'] = $this->formatRatio($yes_value, $no_value);
        $bids = 'Yes: ' . $values['yesValue'] . ' / No: ' . $values['noValue'] . ' / Ratio: ' . $values['ratioValue'] . ' / Net: ' . $values['netYes'];
        
        $yesAvgPrice = ($yes_value / $yes_shares) / 100;
        $noAvgPrice = ($no_value / $no_shares) / 100;
        $values['yesPrice'] = $use_money_format ? $this->formatCents($yesAvgPrice) : $yesAvgPrice * 100;
        $values['noPrice'] = $use_money_format ? $this->formatCents($noAvgPrice) : $noAvgPrice * 100;
        $values['totalPrice'] = $use_money_format ? $this->formatCents(1 - ($yesAvgPrice + $noAvgPrice)) : (1 - ($yesAvgPrice + $noAvgPrice)) * 100;
        $values['ratioPrice'] = $this->formatRatio($yesAvgPrice, $noAvgPrice);
        $prices = ' Yes: ' . $values['yesPrice'] . ' / No: ' . $values['noPrice'] . ' / Ratio: ' . $values['ratioPrice'] . ' / Disc: ' . $values['totalPrice'];

        switch ($type) {
            case 'bids':
                return $bids;
                break;
            case 'prices':
                return $prices;
                break;
            case 'values':
                $this->market_support_yes_side_price = $values['yesPrice'];
                $this->market_support_no_side_price = $values['noPrice'];
                $this->market_support_yes_side_dollars = $values['yesValue'];
                $this->market_support_no_side_dollars = $values['noValue'];
                $this->market_support_net_price_spread = $values['totalPrice'];
                $this->market_support_net_dollars = $values['netYes'];
                $this->market_support_ratio_price = $values['ratioPrice'];
                $this->market_support_ratio_dollars = $values['ratioValue'];
                $this->save();
                return $values;
                break;
        }
    }

    private function formatDollars($dollars)
    {
         return money_format('%#7.0n', $dollars);
    }

    private function formatCents($cents)
    {
         return money_format('%n', $cents);
    }

    private function formatRatio($n1, $n2)
    {
        $sign = $n1 > $n2 ? '+' : '-';
        $min = min($n1, $n2) == 0 ? 1 : min($n1, $n2);
        $ratio = max($n1, $n2) / $min;
        return $sign . number_format($ratio, 1);
    }
}
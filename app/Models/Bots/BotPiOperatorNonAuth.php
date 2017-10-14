<?php

use Sunra\PhpSimple\HtmlDomParser;

class BotPiOperatorNonAuth extends Bot
{
    /**
     * Use Selenium and PredictIt API to get market prices.
     * return XML (inside HTML)
     */
    public function getPiPrices($url)
    {
        $this->makeDriver($url, 'PiPrices');
        $this->driver->get($url);
        return $this->driver->getPageSource();
    }

    /**
     * Visit a PredictIt URL with Selenium. 
     * Make sure the $dom loaded before returning the $dom
     * @todo check that the contest isn't past its close date.
     */
    public function getPiUrlNonAuth($url, $id_element, $identifier = 'id')
    {
        // Cache::forget('PiOperatorNonAuth');
        $this->makeDriver('https://www.predictit.org', 'PiOperatorNonAuth');
        $this->helperGetAUrl($url, $id_element, $identifier);
        usleep(1500000);

        for ($i=0; $i < 7; $i++) { 
            $dom = HtmlDomParser::str_get_html($this->driver->getPageSource());
            if ($dom) {
                break;
            }
            echo $i . "--Dom is missing! " . PHP_EOL;
            usleep(400000);
        }
        return $dom;
    }

    public function findCompetitionQuestions(PiContest $contest)
    {
        $url = str_replace("&#39;", "'", $contest->url_of_answer);
        $dom = $this->getPiUrlNonAuth($url, 'contractList');
        $table = $dom->find('div[id=contractList] table', 0);
        if ($table) {
            foreach ($table->find('tbody tr') as $question) {
                if ($question->find('td')) {
                    $questionLink = $question->find('div[class=outcome-title] a', 0);
                    if ($questionLink) {
                        $values = array();
                        $values['url_of_market'] = 'https://www.predictit.org' . trim($questionLink->href);
                        $values['pi_contest_id'] = $contest->id;

                        $pi = new PiQuestion();
                        $pi->saveQuestion($values);
                    }
                }
            }
        }
        $dom->clear();
        unset($dom);
    }
}

<?php

use Sunra\PhpSimple\HtmlDomParser;

class BotElectionScraper extends Bot
{
    public function getCNN($state)
    {
        $state_name = str_replace(' ', '-', strtolower($state->name));
        $url = 'http://www.cnn.com/election/results/states/' . $state_name;

        $this->makeDriver($url, 'CNN');
        $this->helperGetAUrl($url, 'president', 'id');
        $dom = HtmlDomParser::str_get_html($this->driver->getPageSource());

        $potus = $dom->find('section[id=president]', 0);
        if ($potus) {
            $this->processCNN($state->potusRace(), $potus);
        }

        $senate = $dom->find('section[id=senate]', 0);
        if ($senate) {
            $this->processCNN($state->senateRace(), $senate);
        }

        $governor = $dom->find('section[id=governor]', 0);
        if ($governor) {
            $this->processCNN($state->governorRace(), $governor);
        }

        // House races - need to hover over them, or load them individually.
        $dom->clear();
        unset($dom);
    }

    private function processCNN($race, $data)
    {
        $structured_data = ['dem' => 0, 'gop' => 0, 'lib' => 0, 'ind' => 0, 'other' => 0, ];

        $gop = $data->find('div[class=result-table__row R]', 0);
        $structured_data['gop'] = $gop->find('div[class=result-table__row-item-votes]', 0)->plaintext;

        $dem = $data->find('div[class=result-table__row D]', 0);
        $structured_data['dem'] = $dem->find('div[class=result-table__row-item-votes]', 0)->plaintext;
        
        $other = $data->find('div[class=result-table__row O]', 0);
        if ($other) {
            $structured_data['other'] = $other->find('div[class=result-table__row-item-votes]', 0)->plaintext;
        }
        
        $this->processStructuredData($race, $structured_data, 'CNN');
    }

    private function processStructuredData($race, $structured_data, $source)
    {
      if ($race->votes_dem_cached != $structured_data['dem']) {
        $increment = new ElectionResultIncrement();
        $increment->compareAndSave($race, $structured_data, $source);
      }
    }
}

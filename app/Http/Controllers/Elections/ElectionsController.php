<?php

use App\Jobs\Elections\ScrapeCNN;
use Sunra\PhpSimple\HtmlDomParser;
use Illuminate\Http\Request;

class ElectionsController extends Controller {

    private $scrape_states = [
      'NH',
      'IN',
      'KY',
      'FL',
      'GA',
      'SC',
      'NC',
    ];

    private $others = [
      'AL',
      'AK',
      'AR',
      'AZ',
      'CA',
      'CO',
      'CT',
      'DE',
      'DC',
      'HI',
      'ID',
      'IL',
      'IA',
      'KS',
      'LA',
      'ME',
      'MD',
      'MA',
      'MI',
      'MN',
      'MS',
      'MO',
      'MT',
      'NE',
      'NV',
      'NJ',
      'NM',
      'NY',
      'ND',
      'OH',
      'OK',
      'OR',
      'PA',
      'RI',
      'SD',
      'TN',
      'TX',
      'UT',
      'VT',
      'VA',
      'WA',
      'WV',
      'WI',
      'WY',
    ];

    public function scrapeCNN()
    {
      foreach ($this->scrape_states as $name_short) {
        $state = ElectionState::where('name_short', '=', $name_short)->first();
        $job = (new ScrapeCNN($state))->onQueue('election');
        $this->dispatch($job);
      }
    }

    public function getPotus()
    {
        return View::make('elections.potus')
          ->withData($this->buildStateData('POTUS'))
          ->withAjaxToken(csrf_token())
        ;
    }

    public function getSenate()
    {
        return View::make('elections.senate')
          ->withData($this->buildStateData('Senate'))
          ->withAjaxToken(csrf_token())
        ;
    }

    public function getGovernor()
    {
        return View::make('elections.governor')
          ->withData($this->buildStateData('Governor'))
          ->withAjaxToken(csrf_token())
        ;
    }

    public function getHouse()
    {
        // $state = ElectionState::where('name_short', '=', 'NH')->first();
        // $bot = new BotElectionScraper();
        // $bot->getCNN($state);
        // die();

        // $url = 'http://www.electionprojection.com/house-elections.php';
        // $url = 'https://en.wikipedia.org/wiki/United_States_House_of_Representatives_elections,_2016';
        // $scraper = new Scraper($url);
        // $html = $scraper->html;
        // $list = substr($html, strpos($html, '<h2><span class="mw-headline" id="Complete_list_of_elections">'));

        // $tables = substr_count($list, '<table class="wikitable sortable');

        // for ($i=1; $i < $tables; $i++) { 
        //   $table = substr($list, strpos($list, '<table'));
        //   $table = substr($table, 0, strpos($table, '</table>')+8);
        //   $dom = HtmlDomParser::str_get_html($table);
        //   foreach ($dom->find('tr') as $row) {
        //     $state = $row->find('th', 0)->plaintext;
        //     if (!stristr($state, 'district')) {
        //       $state_name = substr($state, 0, strpos($state, '&#160;'));
        //       $e_state = ElectionState::where('name', '=', $state_name)->first();

        //       $race = new ElectionRace();
        //       $race->election_state_id = $e_state->id;
        //       $race->office = 'House';
        //       $race->district_number = substr($state, strpos($state, '&#160;') + 6);

        //       $pvi = $row->find('td', 0);
        //       $race->party_id_1 = trim($pvi->find('span[class=sorttext]', 0)->plaintext);
              
        //       $incumbent = $row->find('td', 2)->plaintext;
        //       $incumbent_elected = $row->find('td', 3)->plaintext;
        //       $race->incumbent_party = $incumbent . ' (' . $incumbent_elected . ')';

        //       $candidates = $row->find('td', 5)->plaintext;
        //       $can_arr = explode("\n", $candidates);
        //       if ($can_arr) {
        //         foreach ($can_arr as $candidate) {
        //           if (stristr($candidate, 'Republican')) {
        //             $race->gop_name = substr($candidate, 0, strpos($candidate, '(Republican')-1);
        //           } elseif (stristr($candidate, 'Democratic')) {
        //             $race->dem_name = substr($candidate, 0, strpos($candidate, '(Democratic')-1);
        //           }
        //         }
        //       }
              
        //       $race->save();
        //     }
        //   }
        //   $list = substr($list, strpos($list, '</table>')+8);
        // }
        // die();


        return View::make('elections.house')
          ->withData($this->buildStateData('House'))
          ->withAjaxToken(csrf_token())
        ;
    }

    /**
     * [AJAX] Save ElectionRace data.
     * @return Returns a json object with updated projections.
     */
    public function ajaxUpdateRace()
    {
        $race = ElectionRace::find($_POST['race_id']);
        $race->dem_chance_predicted = $_POST['dem_chance'];
        $race->save();

        switch ($race->office) {
          case 'POTUS':
            $divisor = 5.38;
            break;
          case 'Senate': case 'Governor': 
            $divisor = 1;
            break;
          case 'House':
            $divisor = 4.35;
            break;
        }

        $response = [
            'dem_chance' => $race->dem_chance_predicted,
            'data' => $this->buildStateData($race->office),
            'divisor' => $divisor,
        ];
        echo json_encode($response);
    }

    private function buildStateData($office)
    {
        $data = [];
        $data['states'] = ElectionState::all();
        $data['totals']['safe_D'] = 0;
        $data['totals']['likely_D'] = 0;
        $data['totals']['lean_D'] = 0;
        $data['totals']['tossup'] = 0;
        $data['totals']['safe_R'] = 0;
        $data['totals']['likely_R'] = 0;
        $data['totals']['lean_R'] = 0;

        switch ($office) {
          case 'POTUS':
            break;
          case 'Senate':
            $data['totals']['Senate_R'] = 0;
            $data['totals']['Senate_D'] = 0;
            break;
          case 'House':
            $data['totals']['House_R'] = 0;
            $data['totals']['House_D'] = 0;
            break;
        }

        foreach ($data['states'] as $state) {
          switch ($office) {
            case 'POTUS':
              $data['races'][$state->name]['POTUS'] = $state->election_races()->where('office', '=', 'POTUS')->get();
              $data['totals'] = $state->countEVs($data['totals']);
              break;
            case 'Senate':
              $data['totals']['Senate_R'] += $state->countSenators('R');
              $data['totals']['Senate_D'] += $state->countSenators('D');
              $data['totals'] = $state->countLikelySenators($data['totals']);
              $data['races'][$state->name]['Senate'] = $state->election_races()->where('office', '=', 'Senate')->get();
              break;
            case 'Governor':
              $data['races'][$state->name]['Governor'] = $state->election_races()->where('office', '=', 'Governor')->get();
              break;
            case 'House':
              $data['races'][$state->name]['House'] = $state->election_races()->where('office', '=', 'House')->get();
              $data['totals'] = $state->countHouseReps($data['totals']);
              break;
          }
        }
        
        $data['totals']['all_D'] = $data['totals']['safe_D'] + $data['totals']['likely_D'] + $data['totals']['lean_D'];
        $data['totals']['all_D_plus'] = $data['totals']['safe_D'] + $data['totals']['likely_D'] + $data['totals']['lean_D'] + $data['totals']['tossup'];
        $data['totals']['all_R'] = $data['totals']['safe_R'] + $data['totals']['likely_R'] + $data['totals']['lean_R'];
        
        return $data;
    }

    /**
     * Return a json-encoded dataset of Race data to an ajax request.
     */
    public function getAjaxGraph($stat, $race_id)
    {
        $race = ElectionRace::find($race_id);
        $increments = $race->election_result_increments()
                            ->where('ignore_me', '=', 0)
                            ->orderBy('id', 'desc')
                            ->take(15)
                            ->get();

        $data = [];
        $status = $race->raceStatus();
        foreach ($increments as $increment) {
          $increment_data = [];
          $increment_data['date'] = $increment->created_at->toDateTimeString();
          $increment_data[$status] = $increment->demLead($stat);
          $data[] = $increment_data;
        }

        return json_encode($data);
    }

    public function getAjaxPriceQuote($race_id)
    {
        $race = ElectionRace::find($race_id);
        $q = PiQuestion::where('pi_contest_id', '=', $race->pi_contest_id)->first();
        $price_url = 'https://www.predictit.org/api/marketdata/ticker/' . $q->question_ticker;
        $bot = new BotPiOperatorNonAuth();
        $prices = $bot->getPiPrices($price_url);
        $text = substr($prices, stripos($prices, '<Contracts>'));

        $all_data = [];
        $data = [];
        $text = substr($text, strpos($text, '<TickerSymbol>') + 14);
        $data['ticker'] = substr($text, 0, strpos($text, '<'));
        $text = substr($text, strpos($text, '<LastTradePrice>') + 16);
        $data['last'] = substr($text, 0, strpos($text, '<'));
        $text = substr($text, strpos($text, '<BestBuyYesCost>') + 16);
        $data['buyYes'] = substr($text, 0, strpos($text, '<'));
        $text = substr($text, strpos($text, '<BestBuyNoCost>') + 15);
        $data['buyNo'] = substr($text, 0, strpos($text, '<'));
        $text = substr($text, strpos($text, '<BestSellYesCost>') + 17);
        $data['sellYes'] = substr($text, 0, strpos($text, '<'));
        $text = substr($text, strpos($text, '<BestSellNoCost>') + 16);
        $data['sellNo'] = substr($text, 0, strpos($text, '<'));
        $data['summary'] = $data['ticker'] . ': ' . $data['last'] . ' (' . $data['buyYes'] . ' / ' . $data['sellYes'] . ')';
        if (stristr($data['ticker'], 'DEM')) {
          $race->pi_last_dem = $data['summary'];
        } elseif (stristr($data['ticker'], 'GOP') || stristr($data['ticker'], 'REP')) {
          $race->pi_last_gop = $data['summary'];
        }
        $all_data[] = $data;

        if (stristr($text, '<TickerSymbol>')) {
          $text = substr($text, strpos($text, '<TickerSymbol>') + 14);
          $data['ticker'] = substr($text, 0, strpos($text, '<'));
          $text = substr($text, strpos($text, '<LastTradePrice>') + 16);
          $data['last'] = substr($text, 0, strpos($text, '<'));
          $text = substr($text, strpos($text, '<BestBuyYesCost>') + 16);
          $data['buyYes'] = substr($text, 0, strpos($text, '<'));
          $text = substr($text, strpos($text, '<BestBuyNoCost>') + 15);
          $data['buyNo'] = substr($text, 0, strpos($text, '<'));
          $text = substr($text, strpos($text, '<BestSellYesCost>') + 17);
          $data['sellYes'] = substr($text, 0, strpos($text, '<'));
          $text = substr($text, strpos($text, '<BestSellNoCost>') + 16);
          $data['sellNo'] = substr($text, 0, strpos($text, '<'));
          $data['summary'] = $data['ticker'] . ': ' . $data['last'] . ' (' . $data['buyYes'] . ' / ' . $data['sellYes'] . ')';
          if (stristr($data['ticker'], 'DEM')) {
            $race->pi_last_dem = $data['summary'];
          } elseif (stristr($data['ticker'], 'GOP') || stristr($data['ticker'], 'REP')) {
            $race->pi_last_gop = $data['summary'];
          }
          $all_data[] = $data;
        }
        $race->save();

        return json_encode($all_data);
    }

    public function getAjaxVisitMarket($race_id)
    {
        $bot = new TraderBot();
        $race = ElectionRace::find($race_id);
        switch ($race->pi_contest_type) {
          case 'Contest':
            $contest = PiContest::find($race->pi_contest_id);
            $bot->visitContestMarket($contest);
            break;
          case 'Question':
            $question = PiQuestion::find($race->pi_contest_id);
            $bot->visitQuestionMarket($question);
            break;
        }
        return json_encode('OK');
    }
}
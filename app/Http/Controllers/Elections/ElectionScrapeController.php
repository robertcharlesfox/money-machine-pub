<?php

use Smalot\PdfParser\Parser;
use Sunra\PhpSimple\HtmlDomParser;
use GuzzleHttp\Client;
use App\Jobs\SendTextEmail;

class ElectionScrapeController extends Controller {

    public function getFlorida()
    {
      $race = ElectionState::where('name', '=', 'Florida')->first()->potusRace();
      $url = 'https://countyballotfiles.elections.myflorida.com/FVRSCountyBallotReports/AbsenteeEarlyVotingReports/PublicStats';
      $text = $this->clientGet($url);

      $dom = HtmlDomParser::str_get_html($text);
      if ($dom) {
        $table = $dom->find('table', 0);
        if ($table) {
          $mail_row = $table->find('tr', 2);
          $mail_dem = str_replace(",", "", trim($mail_row->find('td', 2)->plaintext));
          $mail_gop = str_replace(",", "", trim($mail_row->find('td', 1)->plaintext));
          $mail_other = str_replace(",", "", trim($mail_row->find('td', 4)->plaintext));

          $early_row = $table->find('tr', 3);
          $early_dem = str_replace(",", "", trim($early_row->find('td', 2)->plaintext));
          $early_gop = str_replace(",", "", trim($early_row->find('td', 1)->plaintext));
          $early_other = str_replace(",", "", trim($early_row->find('td', 4)->plaintext));

          $structured_data['dem'] = $mail_dem + $early_dem;
          $structured_data['gop'] = $mail_gop + $early_gop;
          $structured_data['other'] = $mail_other + $early_other;
          $structured_data['ind'] = 0;
          $structured_data['lib'] = 0;

          $this->processStructuredData($race, $structured_data);
        }
        $dom->clear();
        unset($dom);
      }
    }

    public function getLouisiana()
    {
      $race = ElectionState::where('name', '=', 'Louisiana')->first()->potusRace();
      $url = 'http://electionstatistics.sos.la.gov/Data/Early_Voting_Statistics/Statewide/2016_1108_StatewideStats.pdf';
      $text = $this->getParsedPdf($url);

      $totals = substr($text, strpos($text, 'STATE TOTAL'));
      $data = explode("\n", $totals);
      $structured_data['dem'] = trim($data[7]);
      $structured_data['gop'] = trim($data[8]);
      $structured_data['other'] = trim($data[9]);
      $structured_data['ind'] = 0;
      $structured_data['lib'] = 0;

      $this->processStructuredData($race, $structured_data);
    }

    public function getIowa()
    {
      $race = ElectionState::where('name', '=', 'Iowa')->first()->potusRace();
      $url = 'https://sos.iowa.gov/elections/pdf/2016/general/AbsenteeCongressional2016.pdf';
      $text = $this->getParsedPdf($url);

      $latest = substr($text, 0, strpos($text, "\n\n"));
      $data = explode("\n", $latest);
      $dem_row = $data[23];
      $np_row = $data[24];
      $gop_row = $data[26];
      $structured_data['dem'] = str_replace(",", "", substr($dem_row, strlen($dem_row)-7));
      $structured_data['gop'] = str_replace(",", "", substr($gop_row, strlen($gop_row)-7));
      $structured_data['other'] = str_replace(",", "", substr($np_row, strlen($np_row)-7));
      $structured_data['ind'] = 0;
      $structured_data['lib'] = 0;

      $this->processStructuredData($race, $structured_data);
    }

    public function getNevada()
    {
      $race = ElectionState::where('name', '=', 'Nevada')->first()->potusRace();
      $url = 'http://nvsos.gov/sos/home/showdocument?id=4555';
      $text = $this->getParsedPdf($url);

      $data = explode("\n", $text);
      $structured_data['ind'] = 0;
      $structured_data['lib'] = 0;
      foreach ($data as $row) {
        if (stristr($row, 'Statewide')) {
          $totals = trim(substr($row, strpos($row, ' ')));
          $dem = substr($totals, 0, strpos($totals, ',') + 4);
          $structured_data['dem'] = str_replace(',', '', $dem);

          $totals = str_replace($dem, '', $totals);
          $gop = substr($totals, 0, strpos($totals, ',') + 4);
          $structured_data['gop'] = str_replace(',', '', $gop);

          $totals = str_replace($gop, '', $totals);
          $other = substr($totals, 0, strpos($totals, ',') + 4);
          $structured_data['other'] = str_replace(',', '', $other);
        }
      }

      $this->processStructuredData($race, $structured_data);
    }

    public function getNorthCarolina()
    {
      $race = ElectionState::where('name', '=', 'North Carolina')->first()->potusRace();
      $url = 'http://dl.ncsbe.gov.s3.amazonaws.com/ENRS/absentee11xx08xx2016_Stats.pdf';
      $text = $this->getParsedPdf($url);

      $data = explode("\n", $text);
      $structured_data['ind'] = 0;
      foreach ($data as $row) {
        if (stristr($row, ',')) {
          if (stristr($row, 'Turnout Dem') && stristr($row, 'Registered')) {
            $info = substr($row, strpos($row, ':') + 1);
            $info = substr($info, 0, strpos($info, '%'));
            $info = substr($info, 0, strpos($info, '.')-2);
            $structured_data['dem'] = str_replace(',', '', $info);
          } elseif (stristr($row, 'Turnout Rep') && stristr($row, 'Registered')) {
            $info = substr($row, strpos($row, ':') + 1);
            $info = substr($info, 0, strpos($info, '%'));
            $info = substr($info, 0, strpos($info, '.')-2);
            $structured_data['gop'] = str_replace(',', '', $info);
          } elseif (stristr($row, 'Turnout Una') && stristr($row, 'Registered')) {
            $info = substr($row, strpos($row, ':') + 1);
            $info = substr($info, 0, strpos($info, '%'));
            $info = substr($info, 0, strpos($info, '.')-2);
            $structured_data['other'] = str_replace(',', '', $info);
          } elseif (stristr($row, 'Turnout Lib') && stristr($row, 'Registered')) {
            $info = substr($row, strpos($row, ':') + 1);
            $info = substr($info, 0, strpos($info, '%'));
            $info = substr($info, 0, strpos($info, '.')-2);
            $structured_data['lib'] = str_replace(',', '', $info);
          }
        }
      }

      $this->processStructuredData($race, $structured_data);
    }

    private function processStructuredData($race, $structured_data)
    {
      if ($race->votes_dem_cached != $structured_data['dem']) {
        d('saving new data!');
        $increment = new ElectionResultIncrement();
        $increment->compareAndSave($race, $structured_data, 'State');
      } else {
        d('no new results, try again later!');
      }
    }

    private function getParsedPdf($url)
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($url);
        return $pdf->getText();
    }

    private function clientGet($url)
    {
        $client = new Client;
        $response = $client->get($url);
        return $response->getBody()->getContents();
    }

    private function sendAlerts($state)
    {
        $from = 'EV@mm.dev';
        $subject = $state;
        $body = 'New EV data';
        $job = (new SendTextEmail($from, $subject, $body))->onQueue('texts');
        $this->dispatch($job);
    }
}
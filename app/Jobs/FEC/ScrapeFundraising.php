<?php

namespace App\Jobs\FEC;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;

use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverBy;
use GuzzleHttp\Client;
use Log;
use Cache;

use App\Jobs\SendTextEmail;
use App\Jobs\FEC\ExecuteTradeFundraising;
use PiContest;
use TraderBot;
use PredictIt\CompetitionTrader;

class ScrapeFundraising extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    use DispatchesJobs;

    public $filing_number;
    public $committee_number;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($filing_number, $committee_number)
    {
        // obsolete
        $this->filing_number = $filing_number;
        $this->committee_number = $committee_number;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Cache::forget('FEC');
        // This is just a dummy record to use for saving data.
        $postIt = PiContest::find(186);
        // $postIt->rcp_scrape_frequency = 1096200;
        // $postIt->save();
        $document_number = $postIt->rcp_scrape_frequency+1;
        echo $document_number . "  **  ";
        
        // The committee number is part of the URL but seems unnecessary for loading URL's.
        // $committee_placeholder = 'C00174334';
        $committee_placeholder = 'C00179408';
        // $committee_placeholder = $this->committee_number;
        $committees = [
            'C00580100', 
            'C00581199', 
            'C00575795', 
            'C00605568', 
            'C00003418', 
            'C00010603',
            'C00179408',
            'C00174334',
            'C00425470',
            'C00016899',
            'C00471607',
            'C00541128',
            'C00507913',
            'C00481978',
            'C00484055',
            'C00577130',
        ];
        $random_number = rand(0, 15);
        $committee_placeholder = $committees[$random_number];

        $url = $this->buildUrl($committee_placeholder, $document_number);

        $bot = new TraderBot();
        $bot->makeDriver($url, 'FEC', false, true);
        usleep(400 * 1000);
        $page = $bot->driver->getPageSource();

        Cache::put('FEC', $bot->driver->getSessionID(), 30);

        // Quit if the response is an error message.
        if ($this->pageHasErrorMessage($page)) {
            $postIt->pollingreport_scrape_frequency++;
            $postIt->save();
            echo 'ERROR PAGE: ' . $postIt->pollingreport_scrape_frequency;
            $errors_before_explore_upwards = 15;
            $distance_up = 40;
            if ($postIt->pollingreport_scrape_frequency > $errors_before_explore_upwards) {
                echo 'exploring upwards ';
                $postIt->pollingreport_scrape_frequency = 0;
                $postIt->save();
                for ($i=0; $i < $distance_up; $i++) { 
                    echo $i . ' ';
                    $higher_document_number = $document_number + $i;
                    $url = $this->buildUrl($committee_placeholder, $higher_document_number);
                    $bot->makeDriver($url, 'FEC', false, true);
                    usleep(200 * 1000);
                    $page = $bot->driver->getPageSource();
                    if (!$this->pageHasErrorMessage($page)) {
                        $document_number = $higher_document_number;
                        Log::info('Higher number found: ' . $document_number);
                        echo 'Higher number found: ' . $document_number;
                        break;
                    }
                }
                $bot->makeDriver('http://docquery.fec.gov', 'FEC', false, true);
                usleep(300 * 1000);
                // $postIt->rcp_scrape_frequency = $postIt->rcp_scrape_frequency - 1;
                // $postIt->save();
                Cache::put('FEC', $bot->driver->getSessionID(), 30);
                if ($document_number != $higher_document_number) {
                    return;
                }
            } else {
                return;
            }
        }

        // If it is a filing, save the new max number to our post-it.
        Log::info($document_number);
        $postIt->rcp_scrape_frequency = $document_number;
        $postIt->save();

        // Quit if the committee isn't one we care about.
        $committee = $this->pageHasRelevantCommittee($page);
        if (!$committee) {
            sleep(1);
            return;
        }

        // $month = 'Jul';
        $month = 'Aug';
        $month2 = 'August';
        // If it is, confirm that this is an August monthly filing.
        if (!$this->pageHasRelevantMonthly($page, $month)) {
            sleep(2);
            return;
        }
        $contests = PiContest::where('fundraising_committee', '=', $committee)
            ->where('fundraising_month', '=', $month2)
            ->get()
        ;

        // We're here! Get the fundraising payload and dispatch trades!
        foreach ($contests as $contest) {
            if ($contest->active) {
                $payload = $this->findFundraisingPayload($page, $contest);
                $outcome_values = $this->setNewOutcomeValues($payload, $contest);
                $ct = new CompetitionTrader();
                // $max_risk = 245;
                $max_risk = 350;
                $urgency = 1;
                // $ct->runCompetition($contest, $outcome_values, $max_risk, $urgency);
            }
            // @todo: is this the best way to do this?
            $contest->active = 0;
            $contest->save();
        }

        // @todo: run the $contests loop all over again -- 
        // we know the final answer so we want to keep buying!

        // All done! Great job!
        // sleep(5);

        // Send text email to me with the news.
        $from = 'fecfilings@mm.dev';
        $subject = 'New Filing Trade: ' . $committee;
        $body = $subject;
        $job = (new SendTextEmail($from, $subject, $body))->onQueue('texts');
        $this->dispatch($job);
    }

    private function parsePiQuestionPiId($market_url)
    {
      $market_id = substr($market_url, strpos($market_url, '/', strpos($market_url, 'Contract')) + 1);
      $market_id = substr($market_id, 0, strpos($market_id, '/'));
      return $market_id;
    }

    private function setNewOutcomeValues($payload, $contest)
    {
        $new_values = [];
        $outcomes = $contest->pi_questions;
        foreach ($outcomes as $outcome) {
            if ($payload > $outcome->fundraising_low && $payload < $outcome->fundraising_high) {
                $outcome->chance_to_win = 100;
            } else {
                $outcome->chance_to_win = 0;
            }
            $outcome->save();

            $outcome_pi_id = $this->parsePiQuestionPiId($outcome->url_of_market);
            $new_values[$outcome_pi_id] = $outcome->chance_to_win;
        }
        return $new_values;
    }

    private function findFundraisingPayload($page, $contest)
    {
        switch ($contest->fundraising_description) {
            case '7':
                $line = substr($page, stripos($page, '7. Total'));
                $payload = substr($line, stripos($line, '</TD><TD>')+9);
                $payload = substr($payload, 0, stripos($payload, '</TD>'));
                break;
            
            case '9':
                $line = substr($page, stripos($page, '9. Total'));
                $payload = substr($line, stripos($line, '</TD><TD>')+9);
                $payload = substr($payload, 0, stripos($payload, '</TD>'));
                break;
            
            case '6c':
                $line = substr($page, stripos($page, '(c) Total Receipts'));
                $payload = substr($line, stripos($line, '</td><td')+8);
                $payload = substr($payload, stripos($payload, '>')+1);
                $payload = substr($payload, 0, stripos($payload, '</td>'));
                break;
            
            default:
                $line = '';
                break;
        }
        Log::info($payload);
        echo $payload . "  **  ";
        return $payload;
    }

    private function buildUrl($committee, $number)
    {
        return 'http://docquery.fec.gov/cgi-bin/forms/' . $committee . '/' . $number;
    }

    private function pageHasErrorMessage($page)
    {
        if (stristr($page, 'Internal error #3')) {
            return true;
        }
        return false;
    }

    private function pageHasRelevantCommittee($page)
    {
        $group_id = 'blank';
        if (stripos($page, '<H3>1.')) {
            $group_info = substr($page, stripos($page, '<H3>1.')+6);
            $group_id = substr($group_info, 0, stripos($group_info, '</H3>'));
            Log::info($group_id);
            echo $group_id . "  **  ";
        }

        $committee_info = substr($page, stripos($page, 'candidateCommitteeId')+21);
        $committee_id = substr($committee_info, 0, stripos($committee_info, '&'));
        Log::info($committee_id);
        echo $committee_id . "  **  ";


        // Send text email to me with the news.
        $from = 'fecfilings@mm.dev';
        $subject = 'New Filing: ' . $group_id;
        $body = $subject;
        // $job = (new SendTextEmail($from, $subject, $body))->onQueue('texts');
        // $this->dispatch($job);

        foreach ($this->relevant_committees as $committee => $name) {
            if ($committee == $committee_id) {
                return $committee;
            }
        }
        return false;
    }

    private function pageHasRelevantMonthly($page, $month)
    {
        $report_info = substr($page, stripos($page, 'Report Type')+11);
        $report_type = substr($report_info, 0, stripos($report_info, 'Monthly'));
        if (stristr($report_type, $month)) {
            Log::info($report_type . "  **  " .  'right month');
            echo $report_type . "  **  ";
            echo 'right month' . "  **  ";
            return true;
        }
        Log::info($report_type . "  **  " .  'wrong month');
        echo $report_type . "  **  ";
        echo 'wrong month' . "  **  ";
        return false;
    }

    protected $relevant_committees = [
        'C00580100' => 'Trump', 
        'C00581199' => 'Stein', 
        'C00575795' => 'Clinton', 
        'C00605568' => 'Johnson', 
        'C00003418' => 'RNC', 
        'C00010603' => 'DNC',
    ];
}

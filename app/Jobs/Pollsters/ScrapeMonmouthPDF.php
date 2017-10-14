<?php

namespace App\Jobs\Pollsters;

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
use ScraperBot;
use TraderBot;
use RcpContestPollster;

class ScrapeMonmouthPDF extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    use DispatchesJobs;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $base_url = 'https://www.monmouth.edu/WorkArea/DownloadAsset.aspx?id=';
        $monmouth = RcpContestPollster::where('name', '=', 'Monmouth')->where('pi_contest_id', '=', 1)->first();
        // $last_document_id = 40802211598;
        $last_document_id = $monmouth->last_scrape_other;
        $pings_ahead = 80;

        $bot = new TraderBot();
        echo 'scraping Monmouth PDF';
        for ($i=1; $i < $pings_ahead; $i++) { 
            $document_id = $last_document_id + $i;
            $url = $base_url . $document_id;

            $bot->makeDriver($url, 'Monmouth', false, true);
            usleep(400 * 1000);
            $page = $bot->driver->getPageSource();
            $new_url = $bot->driver->getCurrentUrl();
            Cache::put('Monmouth', $bot->driver->getSessionID(), 30);
            if (strlen($page) == 141) {
                $this->processNewMonmouth($url, $monmouth, $document_id);
                break;
            } elseif ($new_url != $url) {
                $this->processNewMonmouth($url, $monmouth, $document_id);
                break;
            } elseif (stristr($page, 'Access is denied')) {
                // echo $i;
            } else {
                // Unsure
            }
        }
    }

    private function processNewMonmouth($url, $monmouth, $document_id)
    {
        $scraper = new ScraperBot();
        $scraper->keepUpdateBotWarm($url);

        $monmouth->last_scrape_other = $document_id;
        $monmouth->save();

        // Send text email to me with the news.
        $from = 'monmouth@mm.dev';
        $subject = 'New Monmouth Filing';
        $body = $url;
        $job = (new SendTextEmail($from, $subject, $body))->onQueue('texts');
        $this->dispatch($job);
    }
}

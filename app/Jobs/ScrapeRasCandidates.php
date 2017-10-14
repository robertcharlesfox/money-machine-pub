<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use RcpContestPollster;
use ScraperBot;

class ScrapeRasCandidates extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    protected $pollster;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(RcpContestPollster $pollster)
    {
        $this->pollster = $pollster;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // If the pollster needs scraping
        if (strlen($this->pollster->new_poll_update_text) < 3 && $this->pollster->keep_scraping) {
            $time = date('l m/d H:i:s', strtotime('now'));
            echo $time . ' Beginning Scrape for ' . $this->pollster->name . " Candidates \n";
            // Make a new bot and call the scrape
            $bot = new ScraperBot();
            $bot->scrapeRasRightTrackAndCandidates($this->pollster, 'Candidates');
        }
        else {
            echo $this->pollster->name . ' scrape aborted, result already found!' . "\n";
        }
    }
}

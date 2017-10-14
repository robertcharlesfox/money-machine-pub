<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use RcpContestPollster;
use ScraperBot;

class ScrapeSuffolkUsat extends Job implements SelfHandling, ShouldQueue
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
        // Suffolk always gets scraped, since they release numerous pieces. Scraper checks for updated links.
        $time = date('l m/d H:i:s', strtotime('now'));
        echo $time . ' Beginning Scrape for ' . $this->pollster->name . ' via USAT' . "\n";
        // Make a new bot and call the scrape
        $bot = new ScraperBot();
        $bot->scrapeSuffolkUsat($this->pollster);
    }
}

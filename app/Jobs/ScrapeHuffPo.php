<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use ScraperBot;

class ScrapeHuffPo extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

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
        $time = date('l m/d H:i:s', strtotime('now'));
        echo $time . ' Beginning HuffPo Scrape' . "\n";
        // Make a new bot and call the scrape
        $bot = new ScraperBot();
        $bot->scrapeHuffPo();
    }
}

<?php namespace App\Jobs\RCP;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use RcpCandidateScrape;

class ScrapeCandidates extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public $pi_contest_id;
    public $pi_contest_name;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($pi_contest_id, $pi_contest_name)
    {
        $this->pi_contest_id = $pi_contest_id;
        $this->pi_contest_name = $pi_contest_name;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $time_start_ms = strtotime('now');

        $rcp_scrape = new RcpCandidateScrape();
        $rcp_scrape->scrapeRcp($this->pi_contest_id);

        $time_finish_ms = strtotime('now');
        $time_elapsed = $time_finish_ms - $time_start_ms;

        echo 'Scraped RCP Contest ' . $this->pi_contest_name . ' in ' . $time_elapsed . ' ms' . "\n";
    }
}

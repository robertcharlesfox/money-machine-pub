<?php namespace App\Jobs\RCP;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use RcpScrape;
use PiContest;

class ScrapeApproval extends Job implements SelfHandling, ShouldQueue
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

        $scrape = new RcpScrape();
        $scrape->scrape(PiContest::find($this->pi_contest_id));

        $time_finish_ms = strtotime('now');
        $time_elapsed = $time_finish_ms - $time_start_ms;

        echo 'Scraped RCP Approval Contest ' . $this->pi_contest_name . ' in ' . $time_elapsed . ' ms' . "\n";
    }
}

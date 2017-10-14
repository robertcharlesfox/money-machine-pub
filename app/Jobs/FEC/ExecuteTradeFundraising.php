<?php

namespace App\Jobs\FEC;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;

use Log;

use App\Jobs\SendTextEmail;
use TradeFundraising;

class ExecuteTradeFundraising extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    use DispatchesJobs;

    public $trade_url;
    public $yes_or_no;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($trade_url, $yes_or_no)
    {
        $this->trade_url = $trade_url;
        $this->yes_or_no = $yes_or_no;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info($this->trade_url . "  **  " . $this->yes_or_no);
        echo $this->trade_url . "  **  ";
        echo $this->yes_or_no . "  **  ";
        // $trade = new TradeFundraising();
        // $trade->executeFundraisingTrade($this->trade_url, $this->yes_or_no);
    }
}

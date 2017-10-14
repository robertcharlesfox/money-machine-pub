<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use TraderBot;

class TraderBotExecuteCancelOrders extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $trade;

    /**
     * Create a new job instance.
     *
     * @param PiQuestion $trade
     * @return void
     */
    public function __construct($trade)
    {
        $this->trade = $trade;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $bot = new TraderBot();
        $bot->visitQuestionMarket($this->trade, false, true);
        echo "Cancelled orders for " . $this->trade->question_ticker . "\n";
    }
}

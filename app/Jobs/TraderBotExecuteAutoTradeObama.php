<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use TraderBot;

class TraderBotExecuteAutoTradeObama extends Job implements SelfHandling, ShouldQueue
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
        $bot->beast_mode = true;
        $bot->visitQuestionMarket($this->trade, true);
    }
}

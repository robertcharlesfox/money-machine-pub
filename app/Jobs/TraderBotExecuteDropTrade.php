<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use TraderBot;
use RcpDropTrade;

class TraderBotExecuteDropTrade extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $dt;

    /**
     * Create a new job instance.
     *
     * @param PiQuestion $trade
     * @return void
     */
    public function __construct(RcpDropTrade $dt)
    {
        $this->dt = $dt;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // If the trade hasn't already been processed.
        if ($this->dt->auto_trade_me) {
            $time = date('l m/d H:i:s', strtotime('now'));
            echo $time . ' Beginning DropTrade for ' . $this->dt->id . "\n";

            // Make a new bot and call the trade.
            $bot = new TraderBot();
            $bot->executeRcpDropTrade($this->dt);
            $this->dt->auto_trade_me = 0;
            $this->dt->save();
        }
        else {
            echo $this->dt->id . ' DropTrade aborted, already processed!' . "\n";
        }
    }
}

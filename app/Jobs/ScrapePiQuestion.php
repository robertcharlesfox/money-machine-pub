<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use PiQuestion;
use PiMarket;

class ScrapePiQuestion extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    protected $question;
    protected $scrape_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(PiQuestion $question, $scrape_id)
    {
        $this->question = $question;
        $this->scrape_id = $scrape_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        usleep(400000);
        $time = date('l m/d H:i:s', strtotime('now'));
        echo $time . ' Beginning Scrape for ' . $this->question->question_ticker . "\n";

        $market = new PiMarket();
        $market->scrapeQuestionMarket($this->question, $this->scrape_id);

        $time = date('l m/d H:i:s', strtotime('now'));
        echo $time . ' Finished Scrape for ' . $this->question->question_ticker . "\n";
    }
}

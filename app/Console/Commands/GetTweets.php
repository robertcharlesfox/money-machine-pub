<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Phirehose;
use TwitterScraper;

// The OAuth credentials you received when registering your app at Twitter
define("TWITTER_CONSUMER_KEY", env('TWITTER_CONSUMER_KEY'));
define("TWITTER_CONSUMER_SECRET", env('TWITTER_CONSUMER_SECRET'));

// The OAuth data for the twitter account
define("OAUTH_TOKEN", env('TWITTER_OAUTH_TOKEN'));
define("OAUTH_SECRET", env('TWITTER_OAUTH_SECRET'));


class GetTweets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tweets:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Start streaming
        $ts = new TwitterScraper(OAUTH_TOKEN, OAUTH_SECRET, Phirehose::METHOD_USER);
        $ts->consume();
    }

    public function filterExample()
    {
        // Start streaming
        // $ts = new TwitterScraper(OAUTH_TOKEN, OAUTH_SECRET, Phirehose::METHOD_FILTER);
        // $ts->setTrack($this->trackWords);
        // $ts->consume();
    }
}

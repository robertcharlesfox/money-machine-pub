<?php

class TwitterController extends Controller {

    // $table->boolean('is_processed')->default(0);
    // $table->text('tweet_raw_data')->nullable();
    // $table->integer('twitter_tweet_id')->unsigned()->nullable();
    // $table->integer('twitter_user_id')->unsigned()->nullable();
    // $table->string('tweet_text')->nullable();

    // public $trackWords = array(
    //         'the',
    //     );

    public function getPhirehose()
    {
        $tweets = Tweet::all();
        foreach ($tweets as $tweet) {
            $data = json_decode($tweet->tweet_raw_data);
            if (isset($data->text)) {
                d($data->user->screen_name, $data->text, $data);
            }
            else {
                d($data);
            }
        }
    }

}
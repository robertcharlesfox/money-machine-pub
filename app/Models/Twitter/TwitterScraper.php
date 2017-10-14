<?php

define('TWITTER_AP', 426802833);
define('TWITTER_BLOOMBERG', 564111558);
define('TWITTER_CNN', 13850422);
define('TWITTER_IBD_1', 21328656);
define('TWITTER_IBD_2', 1544270486);
define('TWITTER_POLLREPORT', 95368728);
define('TWITTER_PPP', 61220477);
define('TWITTER_WASH_POST', 14703188);

use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\SendTextEmail;

class TwitterScraper extends OauthPhirehose
{
  use DispatchesJobs;

  /**
   * Enqueue each status
   *
   * @param string $status
   */
  public function enqueueStatus($status)
  {
    /*
     * In this simple example, we will just display to STDOUT rather than enqueue.
     * NOTE: You should NOT be processing tweets at this point in a real application, instead they should be being
     *       enqueued and processed asyncronously from the collection process.
     */
    $data = json_decode($status, true);

    if (is_array($data) && isset($data['user']['screen_name'])) {
      $tweet_essentials = $data['user']['screen_name'] . ': ' . urldecode($data['text']);
      $news = PHP_EOL.date("H:i:s ").$tweet_essentials;
      print $news;
      $this->scanTweet($data);
    }

    // $t = new Tweet();
    // $t->tweet_raw_data = $status;
    // $t->save();
  }

  private function scanTweet($data)
  {
    switch ($data['user']['id']) {
      case TWITTER_PPP:
        // $this->checkDataForKeyword($data, 'Poll');
        break;
      
      case TWITTER_WASH_POST:
        // $this->checkDataForKeyword($data, 'Trump');
        break;
      
      case TWITTER_CNN:
        $this->checkDataForKeyword($data, 'CNN/ORC');
        break;
      
      case TWITTER_POLLREPORT:
        // $this->checkDataForKeyword($data, 'CNN/ORC');
        break;
      
      case TWITTER_IBD_1:
        $this->checkDataForKeyword($data, 'IBD/TIPP');
        break;
      
      case TWITTER_IBD_2:
        $this->checkDataForKeyword($data, 'IBD/TIPP');
        break;
      
      case TWITTER_BLOOMBERG:
        $this->checkDataForKeyword($data, 'Poll');
        break;
      
      case TWITTER_AP:
        $this->checkDataForKeyword($data, 'Poll');
        break;
      
      default:
        break;
    }
  }

  private function checkDataForKeyword($data, $keyword)
  {
    if (stristr(urldecode($data['text']), $keyword)) {
      $job = (new SendTextEmail('tweets@mm.dev', 'New TweetPoll', $data['text']))->onQueue('texts');
      $this->dispatch($job);
    }
  }

  /**
   * Basic log function that outputs logging to the standard error_log() handler. This should generally be overridden
   * to suit the application environment.
   *
   * @see error_log()
   * @param string $messages
   * @param String $level 'error', 'info', 'notice'. Defaults to 'notice', so you should set this
   *     parameter on the more important error messages.
   *     'info' is used for problems that the class should be able to recover from automatically.
   *     'error' is for exceptional conditions that may need human intervention. (For instance, emailing
   *          them to a system administrator may make sense.)
   */
  protected function log($message,$level='notice')
  {
    // Log::info('Phirehose Log: ' . $message);
  }
}

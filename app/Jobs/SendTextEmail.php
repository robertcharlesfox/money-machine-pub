<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mail;

class SendTextEmail extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    protected $from;
    protected $subject;
    protected $body;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($from, $subject, $body)
    {
        $this->from = $from;
        $this->subject = $subject;
        $this->body = $body;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $from = $this->from;
        $subject = $this->subject;
        $body = $this->body;

        $time = date('l m/d H:i:s', strtotime('now'));
        echo $time . ' Sending Text Email from ' . $from . ' subject: ' . $subject . "\n";

        for ($i=strlen($body); $i < 465; $i++) { 
            $body .= '-';
        }
        
        Mail::raw("$body", function ($message) use ($from, $subject) {
          $message->from($from, "MM Update");
          // $message->to("@tmomail.net")->subject("$subject");
        });
    }
}

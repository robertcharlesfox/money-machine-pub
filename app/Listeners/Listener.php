<?php

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
/**
 * This works becuase the QueueServiceProvider is modified to look in this namespace.
 * Illuminate\Queue\QueueServiceProvider::registerListener()  just needs to be \Listener
 * Should catch the timeout error and prevent our queue from crashing. Success, it does!!!
 * This is not in version control.
 */
class Listener extends \Illuminate\Queue\Listener {
    public function runProcess(Process $process, $memory)
    {
        try {
            parent::runProcess($process, $memory);
        }
        catch (\Exception $e) {
            Log::notice('Exception, possibly a timeout');
            Log::notice($e->getMessage());
            print "TIMEOUT" . PHP_EOL . PHP_EOL;
        }
        catch (\ErrorException $e) {
            Log::notice('Oh no, ErrorException, maybe fork error');
            Log::notice($e->getMessage());
            print "TIMEOUT" . PHP_EOL . PHP_EOL;
        }
        catch (\ProcessTimedOutException $e) {
            Log::notice('ProcessTimedOutException!!');
            Log::notice($e->getMessage());
            print "TIMEOUT" . PHP_EOL . PHP_EOL;
        }
    }
}

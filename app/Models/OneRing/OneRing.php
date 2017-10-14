<?php

use App\Jobs\SendTextEmail;
use App\Jobs\RCP\ScrapeCandidates;
use App\Jobs\RCP\ScrapeApproval;
use Illuminate\Foundation\Bus\DispatchesJobs;

class OneRing extends Eloquent
{
    use DispatchesJobs;

    public function dispatchNazgul($modulus = 20)
    {
        $current_minute = date('i', strtotime('now'));
        if (!($current_minute % $modulus)) {
            $nazguls = Nazgul::where('active', '=', 1)->get();
            foreach ($nazguls as $nazgul) {
                $nazgul->awaken();
            }
        }
    }

    public function removeNazgulObstacles($nazgul_id)
    {
        $nazgul = Nazgul::find($nazgul_id);
        if ($nazgul->cancel_first) {
            $nazgul->vanish();
        }
    }

    public function releaseNazgulTrade($nazgul_id)
    {
        $nazgul = Nazgul::find($nazgul_id);
        $nazgul->ravage();
    }

    // Get all PiContests with matching dispatch frequency
    public function dispatchRcpScrapers()
    {
        $valid_intervals = $this->getValidMinuteIntervals();
        foreach ($valid_intervals as $interval) {
            $rcp_contests = PiContest::where('category', '=', 'poll_other')
                ->where('active', '=', 1)
                ->where('rcp_scrape_frequency', '=', $interval)
                ->get();
            $this->dispatchRcpScrape($rcp_contests, 'candidates');

            $rcp_contests = PiContest::where('category', '=', 'poll_rcp')
                ->where('active', '=', 1)
                ->where('rcp_scrape_frequency', '=', $interval)
                ->get();
            $this->dispatchRcpScrape($rcp_contests, 'approval');
        }
    }

    // Dispatch multiples of jobs
    private function dispatchRcpScrape($rcp_contests, $job_type)
    {
        foreach ($rcp_contests as $contest) {
            $scrapes_per_minute = $contest->rcp_scrape_frequency == 1 ? max($contest->rcp_scrapes_per_minute, 1) : 1;
            for ($i=0; $i < $scrapes_per_minute; $i++) { 
                $delay = (int) ((60 / $scrapes_per_minute) * $i);
                switch ($job_type) {
                    case 'candidates':
                        $job = (new ScrapeCandidates($contest->id, $contest->name))->delay($delay)->onQueue('rcp');
                        break;
                    case 'approval':
                        $job = (new ScrapeApproval($contest->id, $contest->name))->delay($delay)->onQueue('rcp');
                        break;
                }
                $this->dispatch($job);
            }
        }
    }

	private function getValidMinuteIntervals($valid_intervals = array(1, 2, 5, 10, 15, 20, 30, 60,))
	{
        $current_minute = date('i', strtotime('now'));
        foreach ($valid_intervals as $k => $interval) {
            if ($current_minute % $interval) {
        		unset($valid_intervals[$k]);
        	}
        }
        return $valid_intervals;
	}
}

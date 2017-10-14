<?php

use App\Jobs\FEC\ScrapeFundraising;
use GuzzleHttp\Client;

class FECController extends Controller {

    protected $candidates = [
        'Trump' => [
            'committee_id' => 'C00580100',
            'ranges' => [
                'b1' => [
                    'bottom' => 18000000,
                    'top'    => 99999999999,
                    'url'    => "https://www.predictit.org/Contract/2959/Will-Trump's-monthly-fundraising-for-June-be-%2418M-or-more#data",
                    'priority_losers' => 1,
                ],
                'b2' => [
                    'bottom' => 15000000,
                    'top'    => 17999999.99,
                    'url'    => "https://www.predictit.org/Contract/2960/Will-Trump's-monthly-fundraising-for-June-be-%2415M-to-%2417%2c999%2c99999#data",
                    'priority_losers' => 2,
                ],
                'b3' => [
                    'bottom' => 12000000,
                    'top'    => 14999999.99,
                    'url'    => "https://www.predictit.org/Contract/2958/Will-Trump's-monthly-fundraising-for-June-be-%2412M-to-%2414%2c999%2c99999#data",
                    'priority_losers' => 3,
                ],
                'b4' => [
                    'bottom' => 9000000,
                    'top'    => 11999999.99,
                    'url'    => "https://www.predictit.org/Contract/2957/Will-Trump's-monthly-fundraising-for-June-be-%249M-to-%2411%2c999%2c99999#data",
                    'priority_losers' => 4,
                ],
                'b5' => [
                    'bottom' => 6000000,
                    'top'    => 8999999.99,
                    'url'    => "https://www.predictit.org/Contract/2956/Will-Trump's-monthly-fundraising-for-June-be-%246M-to-%248%2c999%2c99999#data",
                    'priority_losers' => 9,
                ],
                'b6' => [
                    'bottom' => 3000000,
                    'top'    => 5999999.99,
                    'url'    => "https://www.predictit.org/Contract/2955/Will-Trump's-monthly-fundraising-for-June-be-%243M-to-%245%2c999%2c99999#data",
                    'priority_losers' => 9,
                ],
                'b7' => [
                    'bottom' => 0,
                    'top'    => 2999999.99,
                    'url'    => "https://www.predictit.org/Contract/2954/Will-Trump's-monthly-fundraising-for-June-be-below-%243M#data",
                    'priority_losers' => 9,
                ],
            ],
        ],
        'Johnson' => [
            'committee_id' => 'C00605568',
            'ranges' => [
                'b1' => [
                    'bottom' => 1000000,
                    'top'    => 99999999.99,
                    'url'    => "https://www.predictit.org/Contract/2939/Will-Gary-Johnson's-monthly-fundraising-for-June-be-%241M-or-more#data",
                    'priority_losers' => 1,
                ],
                'b2' => [
                    'bottom' => 750000,
                    'top'    => 999999.99,
                    'url'    => "https://www.predictit.org/Contract/2940/Will-Gary-Johnson's-monthly-fundraising-for-June-be-%24750k-to-%24999%2c99999#data",
                    'priority_losers' => 2,
                ],
                'b3' => [
                    'bottom' => 500000,
                    'top'    => 749999.99,
                    'url'    => "https://www.predictit.org/Contract/2941/Will-Gary-Johnson's-monthly-fundraising-for-June-be-%24500k-to-%24749%2c99999#data",
                    'priority_losers' => 3,
                ],
                'b4' => [
                    'bottom' => 250000,
                    'top'    => 499999.99,
                    'url'    => "https://www.predictit.org/Contract/2942/Will-Gary-Johnson's-monthly-fundraising-for-June-be-%24250k-to-%24499%2c99999#data",
                    'priority_losers' => 4,
                ],
                'b5' => [
                    'bottom' => 0,
                    'top'    => 249999.99,
                    'url'    => "https://www.predictit.org/Contract/2943/Will-Gary-Johnson's-monthly-fundraising-for-June-be-below-%24250k#data",
                    'priority_losers' => 5,
                ],
            ],
        ],
        'Stein' => [
            'committee_id' => 'C00581199',
            'ranges' => [
                'b1' => [
                    'bottom' => 250000,
                    'top'    => 999999999.99,
                    'url'    => "https://www.predictit.org/Contract/2986/Will-Jill-Stein's-monthly-fundraising-for-June-be-%24250k-or-more#data",
                    'priority_losers' => 4,
                ],
                'b2' => [
                    'bottom' => 200000,
                    'top'    => 249999.99,
                    'url'    => "https://www.predictit.org/Contract/2987/Will-Jill-Stein's-monthly-fundraising-for-June-be-%24200k-to-%24249%2c99999#data",
                    'priority_losers' => 3,
                ],
                'b3' => [
                    'bottom' => 150000,
                    'top'    => 199999.99,
                    'url'    => "https://www.predictit.org/Contract/2985/Will-Jill-Stein's-monthly-fundraising-for-June-be-%24150k-to-%24199%2c99999#data",
                    'priority_losers' => 1,
                ],
                'b4' => [
                    'bottom' => 100000,
                    'top'    => 149999.99,
                    'url'    => "https://www.predictit.org/Contract/2984/Will-Jill-Stein's-monthly-fundraising-for-June-be-%24100k-to-%24149%2c99999#data",
                    'priority_losers' => 2,
                ],
                'b5' => [
                    'bottom' => 0,
                    'top'    => 99999.99,
                    'url'    => "https://www.predictit.org/Contract/2983/Will-Jill-Stein's-monthly-fundraising-for-June-be-below-%24100k#data",
                    'priority_losers' => 5,
                ],
            ],
        ],
        'Sanders' => [
            'committee_id' => 'P60007168',
            'ranges' => [
                'b1' => [
                    'bottom' => 20000000,
                    'top'    => 99999999999.99,
                    'url'    => "https://www.predictit.org/Contract/2944/Will-Sanders'-monthly-fundraising-for-June-be-%2420M-or-more#data",
                    'priority_losers' => 9,
                ],
                'b2' => [
                    'bottom' => 15000000,
                    'top'    => 19999999.99,
                    'url'    => "https://www.predictit.org/Contract/2945/Will-Sanders'-monthly-fundraising-for-June-be-%2415M-to-%2419%2c999%2c99999#data",
                    'priority_losers' => 4,
                ],
                'b3' => [
                    'bottom' => 10000000,
                    'top'    => 14999999.99,
                    'url'    => "https://www.predictit.org/Contract/2946/Will-Sanders'-monthly-fundraising-for-June-be-%2410M-to-%2414%2c999%2c99999#data",
                    'priority_losers' => 3,
                ],
                'b4' => [
                    'bottom' => 5000000,
                    'top'    => 9999999.99,
                    'url'    => "https://www.predictit.org/Contract/2947/Will-Sanders'-monthly-fundraising-for-June-be-%245M-to-%249%2c999%2c99999#data",
                    'priority_losers' => 1,
                ],
                'b5' => [
                    'bottom' => 0,
                    'top'    => 4999999.99,
                    'url'    => "https://www.predictit.org/Contract/2948/Will-Sanders'-monthly-fundraising-for-June-be-below-%245M#data",
                    'priority_losers' => 2,
                ],
            ],
        ],
    ];

    public function dispatchScrapeFundraising()
    {
        // This is just a dummy record to use for saving data - the highest filing # so far.
        $postIt = PiContest::find(186);
        $document_number = $postIt->rcp_scrape_frequency;

        $scrapes_per_minute = 30;
        $committees = ['C00580100', 'C00581199', 'C00575795', 'C00605568', 'C00003418', 'C00010603',];
        for ($i=0; $i < $scrapes_per_minute; $i++) { 
            $delay = (int) ((60 / $scrapes_per_minute) * $i);
            $job = (new ScrapeFundraising($document_number + 1, $committees[1]))->delay($delay)->onQueue('fundraising');
            $this->dispatch($job);
        }
    }
}

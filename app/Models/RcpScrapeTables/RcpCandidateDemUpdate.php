<?php

class RcpCandidateDemUpdate extends Eloquent {

    public $rcp_url = 'http://www.realclearpolitics.com/epolls/2016/president/us/2016_democratic_presidential_nomination-3824.html';
    public $pi_contest_id = 12;
    // Start this as false, so that updates aren't made as a result of failing to find the RCP table.
    public $scraped_successfully = false;

    public $candidates = array(
            'Clinton',
            'Sanders',
        )
    ;

    public $main_candidates = array(
            'Clinton',
            'Sanders',
        )
    ;

    public $debate_candidates = array(
            'Clinton' => 51.0,
            'Sanders' => 38.3,
        )
    ;

    public $contest_candidates = array(
            'Clinton' => 52,
            'Sanders' => 37,
        )
    ;
}
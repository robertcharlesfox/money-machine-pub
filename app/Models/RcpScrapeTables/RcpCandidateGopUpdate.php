<?php

class RcpCandidateGopUpdate extends Eloquent {

    public $rcp_url = 'http://www.realclearpolitics.com/epolls/2016/president/us/2016_republican_presidential_nomination-3823.html';
    public $pi_contest_id = 13;
    // Start this as false, so that updates aren't made as a result of failing to find the RCP table.
    public $scraped_successfully = false;

    public $candidates = array(
            'Trump',
            'Cruz',
            'Kasich',
        )
    ;

    public $main_candidates = array(
            'Trump',
            'Cruz',
            'Kasich',
        )
    ;

    public $debate_candidates = array(
            'Trump' => 34.5,
            'Cruz' => 19.3,
            'Kasich' => 2.3,
        )
    ;

    public $contest_candidates = array(
            'Trump' => 35,
            'Cruz' => 20,
            'Kasich' => 2,
        )
    ;
}
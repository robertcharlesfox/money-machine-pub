<?php

class NewRcpController extends Controller {

    public function scrapeRcp($pi_contest_id)
    {
        $rcp_scrape = new RcpCandidateScrape();
        $rcp_scrape->scrapeRcp($pi_contest_id);
    }
}

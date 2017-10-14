<?php

class Scraper {

    public $html;

    /**
     * Boilerplate CURL request and response code.
     */
    public function __construct($url, array $params = array())
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:21.0) Gecko/20150701 Firefox/41.0 ',
            CURLOPT_HEADER => false,
            CURLOPT_URL => $url,
        ));
        $this->html = curl_exec($curl);
        curl_close($curl);
    }
}
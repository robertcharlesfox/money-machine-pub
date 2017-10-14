<?php

class Scrape extends Eloquent
{
    public function pi_markets()
    {
        return $this->hasMany(PiMarket::class);
    }
}

<?php

class PiOffer extends Eloquent
{
    public function pi_market()
    {
        return $this->belongsTo(PiMarket::class);
    }
}

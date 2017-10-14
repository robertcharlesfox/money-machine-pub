<?php

class ExecutedAutotrade extends Eloquent
{
    public function pi_question()
    {
        return $this->belongsTo(PiQuestion::class);
    }
}
<?php

class RcpContestPoll extends Eloquent {

    public function rcp_contest_pollster()
    {
        return $this->belongsTo(RcpContestPollster::class);
    }

    public function micha_obama_scrape()
    {
        return $this->hasOne(MichaObamaScrape::class);
    }

    public function rcp_update_add()
    {
        return $this->hasOne(RcpUpdateAdd::class);
    }

    public function rcp_update_drop()
    {
        return $this->hasOne(RcpUpdateDrop::class);
    }

    public function getLastAddAttribute()
    {
        return RcpUpdateAdd::where('rcp_contest_poll_id', '=', $this->id)->get()->last();
    }

    public function getLastDropAttribute()
    {
        return RcpUpdateDrop::where('rcp_contest_poll_id', '=', $this->id)->get()->last();
    }

    public function rcp_update_pollsters()
    {
        return $this->hasMany(RcpUpdatePollster::class);
    }

    public function getTextDateRangeAttribute()
    {
        $start = date('n/d', strtotime($this->date_start));
        $end = date('n/d', strtotime($this->date_end));
        return $start . ' - ' . $end;
    }

    public function getCurrentPollAgeAttribute()
    {
        $first = new DateTime($this->date_end);
        $last = new DateTime();
        return $first->diff($last)->format('%r%a');
    }

    public function getFridayPollAgeAttribute()
    {
        $first = new DateTime($this->date_end);
        $last = new DateTime(date('Y-m-d', strtotime('Friday')));
        return $first->diff($last)->format('%r%a');
    }

    public function getCurrentDaysInAttribute()
    {
        $add = $this->last_add ? new DateTime($this->last_add->rcp_update->local_rcp_timestamp('Y-m-d H:i:s')) : new DateTime();
        $now = new DateTime();
        $length_in_average = $add->diff($now);
        return $length_in_average->format('%a days %h hours');
    }

    public function gallupMinMaxToFit($daily_1, $daily_2, $min_or_max = 'min')
    {
        $other_values = $daily_1->gallupBestGuess() + $daily_2->gallupBestGuess();
        $todays_minimum_total = ($this->percent_favor - 0.5) * 3;
        $todays_maximum_total = ($this->percent_favor + 0.4) * 3;
        return $min_or_max == 'max' ? $todays_maximum_total - $other_values : $todays_minimum_total - $other_values;
    }

    public function gallupThreeDayAverage($daily_1, $daily_2)
    {
        $value_1 = $daily_1->gallupBestGuess();
        $value_2 = $daily_2->gallupBestGuess();
        $value_self = $this->gallupBestGuess();
        return ($value_1 + $value_2 + $value_self) / 3;
    }

    public function gallupTwoDayPreview($daily_1)
    {
        $value_1 = $daily_1->gallupBestGuess();
        $value_self = $this->gallupBestGuess();
        return ($value_1 + $value_self) / 2;
    }

    public function gallupBestGuess()
    {
        if ($this->rcp_contest_pollster_id == 1349) {
            return $this->rasmussen_daily_estimate;
        }
        return $this->gallup_daily_confirmed > 0 ? $this->gallup_daily_confirmed : $this->gallup_daily_estimate;
    }

    public function gallupBestGuessOrActual()
    {
        $best_guess = $this->gallupBestGuess();
        return $best_guess ? $best_guess : $this->percent_favor;
    }

    public function saveDropData()
    {
        $first = new DateTime($this->date_end);
        $last = new DateTime();

        $this->day_of_week_dropped_from_rcp = date('l', strtotime('today'));
        $this->date_dropped_from_rcp_average = date('Y-m-d', strtotime('today'));
        $this->age_of_poll_when_dropped_from_rcp = $first->diff($last)->format('%r%a');
        $this->save();

        $add = new DateTime($this->last_add->rcp_update->local_rcp_timestamp('Y-m-d H:i:s'));
        $drop = new DateTime($this->last_drop->rcp_update->local_rcp_timestamp('Y-m-d H:i:s'));
        $length_in_average = $add->diff($drop);
        $this->length_in_average = $length_in_average->format('%a days %h hours');
        $this->save();
    }
}
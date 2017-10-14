<?php

class ElectionRace extends Eloquent
{
    public function election_state()
    {
        return $this->belongsTo(ElectionState::class);
    }

    public function election_result_increments()
    {
        return $this->hasMany(ElectionResultIncrement::class);
    }

    public function latest_increment()
    {
        return $this->election_result_increments()
                  ->orderBy('id', 'desc')
                  ->first();
    }

    public function piContest()
    {
        if ($this->pi_contest_id) {
            return PiContest::find($this->pi_contest_id);
        }
    }

    public function piLastPrice($ticker)
    {
        if (stristr($ticker, 'DEM')) {
            return $this->pi_last_dem;
        } elseif (stristr($ticker, 'GOP') || stristr($ticker, 'REP')) {
            return $this->pi_last_gop;
        }
    }

    public function raceTotalVotes()
    {
        if (!$this->latest_increment()) {
          return;
        }
        $inc = $this->latest_increment();
        $total_votes_in = $inc->votes_dem_total + $inc->votes_gop_total +
          $inc->votes_independent_total + $inc->votes_libertarian_total + $inc->votes_others_total;
        return $total_votes_in;
    }

    public function raceTurnout()
    {
        $turnout = $this->raceTotalVotes() / $this->election_state->votes_total_2012;
        return (int) ($turnout * 100);
    }

    public function raceStatus()
    {
        if (!$this->latest_increment()) {
          return;
        }
        return number_format($this->latest_increment()->demLead('quantity')) . ' @ ' . $this->raceTurnout() . '%';
    }
}

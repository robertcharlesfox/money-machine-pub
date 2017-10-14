<?php

class ElectionResultIncrement extends Eloquent 
{
    public function election_race()
    {
        return $this->belongsTo(ElectionRace::class);
    }

    public function demLead($stat)
    {
        $margin = $this->votes_dem_total - $this->votes_gop_total;
        switch ($stat) {
          case 'quantity':
            return $margin;

          case 'percent':
            $total = $this->votes_dem_total + $this->votes_gop_total + $this->votes_independent_total +
                $this->votes_libertarian_total + $this->votes_others_total;
            $percent = ($margin / $total) * 100;
            return number_format($percent, 1);
        }
    }

    public function compareAndSave($race, $data, $source)
    {
      $this->election_race_id = $race->id;
      $this->votes_dem_total = $data['dem'];
      $this->votes_gop_total = $data['gop'];
      $this->votes_independent_total = $data['ind'];
      $this->votes_libertarian_total = $data['lib'];
      $this->votes_others_total = $data['other'];
      $this->votes_dem_increment = $data['dem'] - $race->votes_dem_cached;
      $this->votes_gop_increment = $data['gop'] - $race->votes_gop_cached;
      $this->votes_independent_increment = $data['ind'] - $race->votes_independent_cached;
      $this->votes_libertarian_increment = $data['lib'] - $race->votes_libertarian_cached;
      $this->votes_others_increment = $data['other'] - $race->votes_others_cached;
      $this->data_source = $source;
      $this->save();

      $race->votes_dem_cached = $data['dem'];
      $race->votes_gop_cached = $data['gop'];
      $race->votes_independent_cached = $data['ind'];
      $race->votes_libertarian_cached = $data['lib'];
      $race->votes_others_cached = $data['other'];
      $race->save();

      $increment_margin = $this->votes_dem_increment - $this->votes_gop_increment;
      $summary = $race->election_state->name . ' ' . $race->office . ($increment_margin > 0 ? ' +' : ' ') . $increment_margin;
      Log::info($summary);
      echo $summary;
    }
}
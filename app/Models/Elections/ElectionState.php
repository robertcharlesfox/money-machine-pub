<?php

class ElectionState extends Eloquent
{
    public function election_races()
    {
        return $this->hasMany(ElectionRace::class);
    }

    public function election_result_increments()
    {
        return $this->hasManyThrough(ElectionResultIncrement::class, ElectionRace::class);
    }

    public function potusRace()
    {
      return $this->election_races()->where('office', '=', 'POTUS')->first();
    }

    public function senateRace()
    {
      return $this->election_races()->where('office', '=', 'Senate')->first();
    }

    public function governorRace()
    {
      return $this->election_races()->where('office', '=', 'Governor')->first();
    }

    public function houseRaces()
    {
      return $this->election_races()->where('office', '=', 'House')->get();
    }

    public function houseCompetitiveRaces()
    {
      $all = $this->houseRaces();
      $filtered = $all->filter(function ($race) {
          $chance = $race->dem_chance_predicted;
          return $chance !== 0 && $chance != 100;
      });
      return $filtered;
    }

    public function potusStatus()
    {
      if (!$this->potusRace()) {
        return;
      }
      return $this->potusRace()->raceStatus();
    }

    public function getStateLeanColorAttribute()
    {
      if ($this->rank_total > 235) {
        return 'safe-D';
      } elseif ($this->rank_total > 193) {
        return 'likely-D';
      } elseif ($this->rank_total > 165) {
        return 'lean-D';
      } elseif ($this->rank_total == 0) {
        return '';
      } elseif ($this->rank_total < 80) {
        return 'safe-R';
      } elseif ($this->rank_total < 120) {
        return 'likely-R';
      } elseif ($this->rank_total < 145) {
        return 'lean-R';
      }
      return 'tossup';
    }

    public function countSenators($party)
    {
      switch ($party) {
        case 'R':
          return $this->R_Senators_not_on_ballot;
        case 'D':
          return $this->non_R_Senators_not_on_ballot;
      }
    }

    public function countLikelySenators($data)
    {
      $data['safe_D'] += $this->countSenators('D');
      $data['safe_R'] += $this->countSenators('R');

      if (!$this->senateRace()) {
        return $data;
      }

      switch ($this->senateRace()->dem_chance_predicted) {
        case 100:
          $data['safe_D']++;
          break;
        case 85:
          $data['likely_D']++;
          break;
        case 65:
          $data['lean_D']++;
          break;
        case 50:
          $data['tossup']++;
          break;
        case 35:
          $data['lean_R']++;
          break;
        case 15:
          $data['likely_R']++;
          break;
        case 0:
          $data['safe_R']++;
          break;
      }
      return $data;
    }

    public function stateLeanColor($race = 'POTUS')
    {
      switch ($race) {
        case 'POTUS':
          $race = $this->potusRace();
          break;
        case 'Senate':
          $race = $this->senateRace();
          break;
        case 'Governor':
          $race = $this->governorRace();
          break;
      }
      if (!$race) {
        return 'Black';
      }
      switch ($race->dem_chance_predicted) {
        case 100:
          return 'DarkBlue';
        case 85:
          return 'DodgerBlue';
        case 65:
          return 'DeepSkyBlue';
        case 50:
          return 'DarkKhaki';
        case 35:
          return 'LightCoral';
        case 15:
          return 'FireBrick';
        case 0:
          return 'DarkRed';
      }
    }

    public function countEVs($data)
    {
      if (!$this->potusRace()) {
        return $data;
      }
      switch ($this->potusRace()->dem_chance_predicted) {
        case 100:
          $data['safe_D'] += $this->electoral_votes;
          break;
        case 85:
          $data['likely_D'] += $this->electoral_votes;
          break;
        case 65:
          $data['lean_D'] += $this->electoral_votes;
          break;
        case 50:
          $data['tossup'] += $this->electoral_votes;
          break;
        case 35:
          $data['lean_R'] += $this->electoral_votes;
          break;
        case 15:
          $data['likely_R'] += $this->electoral_votes;
          break;
        case 0:
          $data['safe_R'] += $this->electoral_votes;
          break;
      }
      return $data;
    }

    public function countHouseReps($data)
    {
      if ($this->houseRaces()->count() == 0) {
        return $data;
      }
      foreach ($this->houseRaces() as $house_race) {
        switch ($house_race->dem_chance_predicted) {
          case 100:
            $data['safe_D']++;
            break;
          case 85:
            $data['likely_D']++;
            break;
          case 65:
            $data['lean_D']++;
            break;
          case 50:
            $data['tossup']++;
            break;
          case 35:
            $data['lean_R']++;
            break;
          case 15:
            $data['likely_R']++;
            break;
          case 0:
            $data['safe_R']++;
            break;
        }
      }
      return $data;
    }
}

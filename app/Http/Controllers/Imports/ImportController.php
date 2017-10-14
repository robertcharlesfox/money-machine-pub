<?php

class ImportController extends Controller 
{
    public function getImport()
    {
        $filename = public_path() . "/import/State POTUS Details - Copy of data.csv";
        $data = explode("\r", File::get($filename));
        // d($data);
        foreach ($data as $row) {
          // $this->makeState($row);
          // $this->makePresidentialRace($row);
        }
    }

    private function makeState($row)
    {
        $state_data = explode(",", $row);
        $state = new ElectionState();
        $state->name = trim($state_data[0]);
        $state->name_short = $state_data[1];
        $state->rank_total = $state_data[2];
        $state->electoral_method = $state_data[3];
        $state->electoral_votes = $state_data[4];
        $state->time_polls_close = $state_data[5];
        if ($state_data[6]) {
          $date = date('Y-m-d', strtotime($state_data[6]));
          $state->early_vote_begins = $date;
        }
        $state->percent_white = $state_data[7];
        $state->percent_black = $state_data[8];
        $state->percent_bachelors = $state_data[9];
        $state->votes_dem_2012 = $state_data[10];
        $state->votes_gop_2012 = $state_data[11];
        $state->votes_total_2012 = $state_data[12];
        $state->votes_dem_2008 = $state_data[13];
        $state->votes_gop_2008 = $state_data[14];
        $state->votes_total_2008 = $state_data[15];
        $state->votes_dem_1996 = $state_data[16];
        $state->votes_gop_1996 = $state_data[17];
        $state->votes_total_1996 = $state_data[18];
        $state->R_Senators_not_on_ballot = $state_data[19];
        $state->non_R_Senators_not_on_ballot = $state_data[20];
        // $state->save();
    }

    private $names_to_skip = ['Total', 'Maine 1', 'Nebraska 1', 'Nebraska 3',];
    private function makePresidentialRace($row)
    {
        $state_data = explode(",", $row);
        $name = trim($state_data[0]);
        if (!in_array($name, $this->names_to_skip)) {
          $state = ElectionState::where('name', '=', $name)->first();
          $race = new ElectionRace();
          $race->election_state_id = $state->id;
          $race->office = 'POTUS';
          $race->percent_white = $state_data[7];
          $race->percent_black = $state_data[8];
          $race->percent_bachelors = $state_data[9];
          $race->votes_dem_last_time = $state_data[10];
          $race->votes_gop_last_time = $state_data[11];
          $race->votes_total_last_time = $state_data[12];
          // $race->save();
        }
    }
}
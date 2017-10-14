<?php

use Illuminate\Http\Request;

class AjaxController extends Controller {

    protected $candidate_names = [
        'Clinton',
        'Trump',
        'Johnson',
        'Stein',
        'spread',
    ];

    /**
     * Return a json-encoded dataset of PiOffers to an ajax request.
     */
    public function getAjaxBubbles($question_id)
    {
        $i=1;
        $data = array();
        $markets = PiMarket::where('pi_question_id', '=', $question_id)
            ->orderBy('id', 'desc')
            ->take(15)
            ->get();
        foreach ($markets as $market) {
          $marray = array('name' => $i);
          
          foreach ($market->pi_offers as $offer) {
            $oarray = array(
              'action' => $offer->action,
              'price' => $offer->price,
              'shares' => $offer->shares,
              'timestamp' => date('n/j h:i a', strtotime($market->created_at)),
            );
            $marray['children'][] = $oarray;
          }

          $last_array = array(
            'action' => 'lastPrice',
            'price' => $market->last_price,
            'shares' => 500,
            'timestamp' => date('n/j h:i a', strtotime($market->created_at)),
          );
          $marray['children'][] = $last_array;

          $data['children'][] = $marray;
          $i++;
        }
        return json_encode($data);
    }

    /**
     * [AJAX] Save RcpContestPollster data.
     * @return Returns a json object with updated projections.
     */
    public function ajaxSavePollster()
    {
        $pollster = RcpContestPollster::find($_POST['pollster_id']);
        $pollster->probability_added = $_POST['probability_added'];
        $pollster->probability_dropped = $_POST['probability_dropped'];
        $pollster->probability_updated = $_POST['probability_updated'];
        $pollster->projected_result = $_POST['projected_result'];
        $pollster->update_frequency = $_POST['update_frequency'];
        foreach ($this->candidate_names as $name) {
            $field_name = 'early_' . $name;
            if (isset($_POST[$field_name])) {
                $pollster->$field_name = $_POST[$field_name];
            }
        }
        $pollster->cached_values = '';
        $pollster->save();

        $contest = $pollster->pi_contest;
        $new_values = $pollster->valuesForAverage(true, false, $contest->implied_bias, 
            $contest->implied_variance, $contest->getContestMagicStrings('column'));
        
        $contest_values = $contest->getCurrentContestValues();

        $pi_contract_new_values = [];
        foreach ($contest_values['questions'] as $question) {
            $pi_contract_new_values[] = ['id' => $question->id, 'value' => $question->chance_to_win,];
        }

        $response = [
            'new_values' => $new_values,
            'pi_contract_new_values' => $pi_contract_new_values,
            'rcp_all_inclusive' => $contest_values['projections']['market']['all_inclusive'],
            'rcp_un_adjusted' => $contest_values['projections']['straight']['all_inclusive'],
        ];
        echo json_encode($response);
    }

}
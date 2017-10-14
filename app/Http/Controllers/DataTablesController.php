<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Datatables;
use PiContest;

class DataTablesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('admin.examples.datatables');
    }

    public function data()
    {
        $players = PiContest::find(80)
            ->pi_questions()
            ->where('active', '=', 1)
            ->select(['id', 'question_ticker', 'chance_to_win', 'churn_range']);

        return Datatables::of($players)
            ->add_column('actions','<a href="{{ route(\'admin.users.show\', $id) }}"><i class="livicon" data-name="info" data-size="18" data-loop="true" data-c="#428BCA" data-hc="#428BCA" title="view user"></i></a>
                                    <a href="{{ route(\'admin.users.edit\', $id) }}"><i class="livicon" data-name="edit" data-size="18" data-loop="true" data-c="#428BCA" data-hc="#428BCA" title="update user"></i></a>
                                   ')
            ->make(true);
        // make(true) is needed to receive the headers/column names in the response
    }
}

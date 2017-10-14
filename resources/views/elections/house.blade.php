@extends('josh/layouts/nobottom')
@section('title')
    House
    @parent
@stop
@section('header_styles')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/frontend/faq.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/datatables/extensions/ColReorder/css/colReorder.bootstrap.min.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/datatables/extensions/Scroller/css/scroller.bootstrap.min.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/datatables/extensions/RowReorder/css/rowReorder.bootstrap.min.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/datatables/extensions/TableTools/css/dataTables.tableTools.min.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/datatables/extensions/Responsive/css/responsive.dataTables.min.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/datatables/extensions/bootstrap/dataTables.bootstrap.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/pages/tables.css') }}" />

<link rel="stylesheet" type="text/css" href="{{ asset('css/elections.css') }}" />
@stop

@section('content')
<div class="container">
<div class="well">
    <div class="row">
        <div class="col-md-3">
            <label class="control-label" for="graph-race-id">Graph</label>
            <select id="graph-race-id" name="graph-race-id" class="form-control">
              @foreach ($data['states'] as $state)
                @if ($state->houseRaces()->count())
                    @foreach ($state->houseRaces() as $house_race)
                    @if ($house_race->raceTotalVotes())
                    <option value="{{ $house_race->id }}">{{ $state->name }}</option>
                    @endif
                    @endforeach
                @endif
              @endforeach
            </select>
            <button onclick="getLineData('quantity')" class="btn btn-xs btn-primary">#</button>
            <button onclick="getLineData('percent')" class="btn btn-xs btn-info">%</button>
            <button onclick="getPriceQuote(0)" class="btn btn-xs btn-success">$</button>
            <button onclick="getVisitMarket(0)" class="btn btn-xs btn-default">Visit</button>
            <div id="price-data"></div>
        </div>
        <div class="col-lg-9" align="center">
            <button id="show-table" class="btn btn-xs btn-warning">Table</button>
            <button id="show-panels" class="btn btn-xs btn-warning">Panels</button>
        </div>
    </div>

    <div class="row" id="graph-here" style="display: none"><div class="col-md-12">
    </div></div>

    <div class="row">
        <div class="col-lg-12">
            <h2>House: 
                <span id="all-D">{{ $data['totals']['all_D'] }}</span> - 
                <span id="all-R">{{ $data['totals']['all_R'] }}</span>
                  (Max D: 
                <span id="all-D-plus">{{ $data['totals']['all_D_plus'] }}</span>)
            </h2>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12" align="center">
        <div class="progress">
          <div id="safe-D" class="progress-bar safe-D" style="width: {{ $data['totals']['safe_D']/4.35 }}%">
            <span>{{ $data['totals']['safe_D'] }}</span>
          </div>
          <div id="likely-D" class="progress-bar likely-D" style="width: {{ $data['totals']['likely_D']/4.35 }}%">
            <span>{{ $data['totals']['likely_D'] }}</span>
          </div>
          <div id="lean-D" class="progress-bar lean-D" style="width: {{ $data['totals']['lean_D']/4.35 }}%">
            <span>{{ $data['totals']['lean_D'] }}</span>
          </div>
          <div id="tossup" class="progress-bar tossup progress-bar-striped" style="width: {{ $data['totals']['tossup']/4.35 }}%">
            <span>{{ $data['totals']['tossup'] }}</span>
          </div>
          <div id="lean-R" class="progress-bar lean-R" style="width: {{ $data['totals']['lean_R']/4.35 }}%">
            <span>{{ $data['totals']['lean_R'] }}</span>
          </div>
          <div id="likely-R" class="progress-bar likely-R" style="width: {{ $data['totals']['likely_R']/4.35 }}%">
            <span>{{ $data['totals']['likely_R'] }}</span>
          </div>
          <div id="safe-R" class="progress-bar safe-R" style="width: {{ $data['totals']['safe_R']/4.35 }}%">
            <span>{{ $data['totals']['safe_R'] }}</span>
          </div>
        </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div id="states-table" class="panel panel-primary filterable" style="display:none">
                <div class="panel-body table-responsive">
                    <table class="table table-striped table-bordered" id="table1">
                        <thead>
                            <tr>
                                <th>District</th>
                                <th>PVI</th>
                                <th>Incumbent</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data['states'] as $state)
                            @foreach ($data['races'][$state->name]['House'] as $race)
                            @if($race->dem_chance_predicted != 100 && $race->dem_chance_predicted !== 0)
                            <tr>
                                <td>{{ $race->election_state->name . '-' . $race->district_number }}</td>
                                <td>{{ $race->party_id_1 }}</td>
                                <td>
                                    {{ $race->incumbent_party }}
                                    <button data-race-id="{{ $race->id }}" data-dem-chance="100" class="btn btn-xs race-update safe-D">Safe D</button>
                                    <button data-race-id="{{ $race->id }}" data-dem-chance="0" class="btn btn-xs race-update safe-R">Safe R</button>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div id="states-panels"><div id="faq">
                @foreach ($data['states'] as $state)
                @if($state->houseCompetitiveRaces()->count())
                <div class="mix category-1 col-lg-12 panel panel-default {{ $state->state_lean_color }}" data-value="1">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a class="collapsed" data-toggle="collapse" href="#state{{ $state->id }}">
                                <strong class="c-gray-light">{{ $state->name }}: </strong>{{ $state->time_polls_close }}
                                <span class="badge pull-right" style="background-color:DarkRed">{{ $state->countSenators('R') }}</span>
                                <span class="badge pull-right" style="background-color:DarkBlue">{{ $state->countSenators('D') }}</span>
                                <p>{{ $state->early_vote_begins }}</p>
                            </a>
                        </h4>
                    </div>
                    <div id="state{{ $state->id }}" class="panel-collapse collapse">
                        <ul class="list-group">
                          <li class="list-group-item">
                            @foreach ($data['races'][$state->name]['House'] as $race)
                            @if($race->dem_chance_predicted != 100 && $race->dem_chance_predicted !== 0)
                            <div class="row">
                                <div class="col-md-4">
                                    <h4>House: 
                                        <span id="race-dem-chance-{{ $race->id }}">{{ $race->dem_chance_predicted }}</span>% Dem Win
                                    </h4>
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>{{ $race->district_number }}</th>
                                                <th>Dem</th>
                                                <th>GOP</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>2016</td>
                                                <td>{{ number_format($race->votes_dem_cached) }}</td>
                                                <td>{{ number_format($race->votes_gop_cached) }}</td>
                                                <td>{{ number_format($race->votes_dem_cached + $race->votes_gop_cached + $race->votes_independent_cached + $race->votes_others_cached) }}</td>
                                            </tr>
                                            <tr>
                                                <td>2014</td>
                                                <td>{{ number_format($state->votes_dem_last_time) }}</td>
                                                <td>{{ number_format($state->votes_gop_last_time) }}</td>
                                                <td>{{ number_format($state->votes_total_last_time) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-8">
                                    <div class="row"><div class="col-md-12">
                                        <h4>{{ $state->name_short . '-' . $race->district_number }}</h4>
                                        <h5>{{ $race->dem_name . ' vs ' . $race->gop_name }}</h5>
                                        <h5>{{ $race->incumbent_party . ' - ' . $race->party_id_1 }}</h5>
                                    </div></div>
                                    <div class="row"><div class="col-md-12">
                                        <button data-race-id="{{ $race->id }}" data-dem-chance="100" class="btn btn-xs race-update safe-D">Safe D</button>
                                        <button data-race-id="{{ $race->id }}" data-dem-chance="85" class="btn btn-xs race-update likely-D">Likely D</button>
                                        <button data-race-id="{{ $race->id }}" data-dem-chance="65" class="btn btn-xs race-update lean-D">Lean D</button>
                                        <button data-race-id="{{ $race->id }}" data-dem-chance="50" class="btn btn-xs race-update tossup">Tossup</button>
                                        <button data-race-id="{{ $race->id }}" data-dem-chance="35" class="btn btn-xs race-update lean-R">Lean R</button>
                                        <button data-race-id="{{ $race->id }}" data-dem-chance="15" class="btn btn-xs race-update likely-R">Likely R</button>
                                        <button data-race-id="{{ $race->id }}" data-dem-chance="0" class="btn btn-xs race-update safe-R">Safe R</button>
                                    </div></div>
                                    @if ($race->piContest())
                                    <div class="row"><div class="col-md-12">
                                        <button onclick="getPriceQuote({{ $race->id }})" class="btn btn-xs btn-success">$</button>
                                        <button onclick="getVisitMarket({{ $race->id }})" class="btn btn-xs btn-default">Visit</button>
                                        <div id="price-data-{{ $race->id }}">
                                        @foreach($race->piContest()->pi_questions as $question)
                                            {{ $race->piLastPrice($question->question_ticker) }}
                                            <br>
                                        @endforeach
                                        </div>
                                    </div></div>
                                    @endif
                                </div>
                            </div>
                            @endif
                            @endforeach
                          </li>
                        </ul>
                    </div>
                </div>
                @endif
                @endforeach
            </div></div>
        </div>
    </div>
</div>
</div>
@stop

@section('footer_scripts')
    @include('elections.inc.elections-js')
@stop

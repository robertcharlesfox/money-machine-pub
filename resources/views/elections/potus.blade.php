@extends('josh/layouts/nobottom')
@section('title')
    POTUS
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
        <div class="col-md-4">
            <label class="control-label" for="graph-race-id">Graph</label>
            <select id="graph-race-id" name="graph-race-id" class="form-control">
              @foreach ($data['states'] as $state)
                @if ($state->potusRace())
                    @if ($state->potusRace()->raceTotalVotes())
                    <option value="{{ $state->potusRace()->id }}">{{ $state->name }}</option>
                    @endif
                @endif
              @endforeach
            </select>
            <button onclick="getLineData('quantity')" class="btn btn-xs btn-primary">#</button>
            <button onclick="getLineData('percent')" class="btn btn-xs btn-info">%</button>
            <button onclick="getPriceQuote(0)" class="btn btn-xs btn-success">$</button>
            <button onclick="getVisitMarket(0)" class="btn btn-xs btn-default">Visit</button>
            <div id="price-data"></div>
        </div>
        <div class="col-lg-8" align="center">
            <button id="show-table" class="btn btn-xs btn-warning">Table</button>
            <button id="show-panels" class="btn btn-xs btn-warning">Panels</button>
        </div>
    </div>

    <div class="row" id="graph-here"><div class="col-md-12">
    </div></div>

    <div class="row">
        <div class="col-lg-12">
            <h2>POTUS: 
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
          <div id="safe-D" class="progress-bar safe-D" style="width: {{ $data['totals']['safe_D']/5.38 }}%">
            <span>{{ $data['totals']['safe_D'] }}</span>
          </div>
          <div id="likely-D" class="progress-bar likely-D" style="width: {{ $data['totals']['likely_D']/5.38 }}%">
            <span>{{ $data['totals']['likely_D'] }}</span>
          </div>
          <div id="lean-D" class="progress-bar lean-D" style="width: {{ $data['totals']['lean_D']/5.38 }}%">
            <span>{{ $data['totals']['lean_D'] }}</span>
          </div>
          <div id="tossup" class="progress-bar tossup progress-bar-striped" style="width: {{ $data['totals']['tossup']/5.38 }}%">
            <span>{{ $data['totals']['tossup'] }}</span>
          </div>
          <div id="lean-R" class="progress-bar lean-R" style="width: {{ $data['totals']['lean_R']/5.38 }}%">
            <span>{{ $data['totals']['lean_R'] }}</span>
          </div>
          <div id="likely-R" class="progress-bar likely-R" style="width: {{ $data['totals']['likely_R']/5.38 }}%">
            <span>{{ $data['totals']['likely_R'] }}</span>
          </div>
          <div id="safe-R" class="progress-bar safe-R" style="width: {{ $data['totals']['safe_R']/5.38 }}%">
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
                                <th>State</th>
                                <th>EVs</th>
                                <th>Rank</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data['states'] as $state)
                            <tr>
                                <td class="{{ $state->state_lean_color }}">{{ $state->name }}</td>
                                <td>{{ $state->electoral_votes }}</td>
                                <td>{{ $state->rank_total }}</td>
                            </tr>
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
                <div class="mix category-1 col-lg-12 panel panel-default {{ $state->state_lean_color }}" data-value="1">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a class="collapsed" data-toggle="collapse" href="#state{{ $state->id }}">
                                <strong class="c-gray-light">{{ $state->name }}: </strong>{{ $state->time_polls_close }}
                                <span class="badge pull-right" style="background-color:DarkRed">{{ $state->countSenators('R') }}</span>
                                <span class="badge pull-right" style="background-color:DarkBlue">{{ $state->countSenators('D') }}</span>
                                <span class="badge pull-right" style="background-color:{{ $state->stateLeanColor() }}">{{ $state->electoral_votes }} EV</span>
                                <p>{{ $state->early_vote_begins }}</p>
                                <p>{{ $state->potusStatus() }}</p>
                            </a>
                        </h4>
                    </div>
                    <div id="state{{ $state->id }}" class="panel-collapse collapse">
                        <ul class="list-group">
                          <li class="list-group-item">
                            @foreach ($data['races'][$state->name]['POTUS'] as $race)
                            <div class="row">
                                <div class="col-md-4">
                                    <h4>POTUS: 
                                        <span id="race-dem-chance-{{ $race->id }}">{{ $race->dem_chance_predicted }}</span>% HRC Win
                                    </h4>
                                    @include('elections.inc.state-race-data')
                                </div>
                            </div>
                            @endforeach
                          </li>
                        </ul>
                    </div>
                </div>
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

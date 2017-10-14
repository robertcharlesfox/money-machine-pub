@extends('josh/layouts/nobottom')
@section('title')
    Governor
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
                @if ($state->governorRace())
                    @if ($state->governorRace()->raceTotalVotes())
                    <option value="{{ $state->governorRace()->id }}">{{ $state->name }}</option>
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
    </div>

    <div class="row" id="graph-here" style="display: none"><div class="col-md-12">
    </div></div>

    <div class="row">
        <div class="col-md-12">
            <div id="states-panels"><div id="faq">
                @foreach ($data['states'] as $state)
                @if($state->governorRace())
                <div class="mix category-1 col-lg-12 panel panel-default {{ $state->state_lean_color }}" data-value="1">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a class="collapsed" data-toggle="collapse" href="#state{{ $state->id }}">
                                <strong class="c-gray-light">{{ $state->name }}: </strong>{{ $state->time_polls_close }}
                                <span class="badge pull-right" style="background-color:DarkRed">{{ $state->countSenators('R') }}</span>
                                <span class="badge pull-right" style="background-color:DarkBlue">{{ $state->countSenators('D') }}</span>
                                <span class="badge pull-right" style="background-color:{{ $state->stateLeanColor('Governor') }}">{{ $state->governorRace()->dem_chance_predicted }}%</span>
                                <p>{{ $state->early_vote_begins }}</p>
                            </a>
                        </h4>
                    </div>
                    <div id="state{{ $state->id }}" class="panel-collapse collapse">
                        <ul class="list-group">
                          <li class="list-group-item">
                            @foreach ($data['races'][$state->name]['Governor'] as $race)
                            <div class="row">
                                <div class="col-md-4">
                                    <h4>Governor: 
                                        <span id="race-dem-chance-{{ $race->id }}">{{ $race->dem_chance_predicted }}</span>% Dem Win
                                    </h4>
                                    @include('elections.inc.state-race-data')
                                </div>
                            </div>
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

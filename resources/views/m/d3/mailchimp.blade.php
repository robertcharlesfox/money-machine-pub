@extends('fmw.master')

@section('title', 'Super Admin Data Visualizations')

@section('primary')
<div class="col-xs-12 admin-stats">
  <div class="panel panel-fmw">
    <div class="panel-heading">
      <h3 class="panel-title">Mailchimp Campaign Stats</h3>
    </div>
    <ul class="list-group">
      <li class="list-group-item">
        <h5>Member Count</h5>
        <div class="row">
          <div class="col-sm-4">
            @foreach($lists as $list)
            {{ $list['name'] }}
            <div class="campaign-member-count" data-value="{{ $list['stats']['member_count'] }}"></div>
            @endforeach
          </div>
          <div class="col-sm-4">
            <div id="member-count" class="chart"></div>
          </div>
        </div>
      </li>
      <li class="list-group-item">
        <h5>Open Rates</h5>
        <div class="row">
          <div class="col-sm-4">
            @foreach($lists as $list)
            {{ $list['name'] }}
            <div class="campaign-open-rate" data-value="{{ number_format($list['stats']['open_rate']) }}"></div>
            @endforeach
          </div>
          <div class="col-sm-4">
            <div id="open-rate" class="chart"></div>
          </div>
        </div>
      </li>
      <li class="list-group-item">
        <h5>Click Rates</h5>
        <div class="row">
          <div class="col-sm-4">
            @foreach($lists as $list)
            {{ $list['name'] }}
            <div class="campaign-click-rate" data-value="{{ number_format($list['stats']['click_rate']) }}"></div>
            @endforeach
          </div>
          <div class="col-sm-4">
            <div id="click-rate" class="chart"></div>
          </div>
        </div>
      </li>
    </ul>
  </div>
</div>
@stop

@section('scripts')
{{ HTML::script('/js/d3.min.js') }}
{{ HTML::script('/js/d3s.js') }}
@stop
@extends('fmw.master')

@section('title', 'Super Admin Data Visualizations')

@section('primary')
<style>
  body {
    font: 10px sans-serif;
  }
  .axis path,
  .axis line {
    fill: none;
    stroke: #000;
    shape-rendering: crispEdges;
  }
  .line {
    fill: none;
    stroke: red;
    stroke-width: 5px;
  }
  .area {
    fill: steelblue;
  }
</style>
<h3>FMW User Accounts vs Time</h3>
<div id="fmw-user-graph"></div>
@stop

@section('scripts')
{{ HTML::script('js/d3.min.js') }}
{{ HTML::script('js/userSignups.js') }}
@stop
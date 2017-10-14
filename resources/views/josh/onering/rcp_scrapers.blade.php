@extends('josh/layouts/nobottom')

@section('title')
One Ring RCP Scrapers
@parent
@stop

@section('content')
<div class="container">
    @foreach ($approval_contests as $contest)
      @include('josh.onering.inc-rcp-scraper-form')
    @endforeach
    @foreach ($candidate_contests as $contest)
      @include('josh.onering.inc-rcp-scraper-form')
    @endforeach
</div>
@stop

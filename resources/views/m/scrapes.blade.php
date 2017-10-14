@extends('m.base')
@section('content')
  <h2>Last 7 RCP Updates</h2>
  @foreach ($rcp_contests as $contest)
    <h3>{{ $contest->name }}</h3>
    <div class="row">
      <strong>
      <div class="col-md-2">Update Timestamp</div>
      <div class="col-md-2">RCP Average</div>
      <div class="col-md-2">Update # Today</div>
      <div class="col-md-6">Update Text</div>
      </strong>
    </div>
    @foreach ($contest->rcp_scrape_updates() as $scrape)
    <div class="row">
      <div class="col-md-2">{{ $scrape->created_at }}</div>
      <div class="col-md-2">{{ $scrape->average }}</div>
      <div class="col-md-2">{{ $scrape->update_number_today }}</div>
      <div class="col-md-6">{{ $scrape->update_text }}</div>
    </div>
    @endforeach
  @endforeach
@stop

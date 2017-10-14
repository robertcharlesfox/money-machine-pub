@extends('m.base')
@section('content')
  <h1>PredictIt Market Depth</h1>
  <table class="table table-striped table-hover">
    <thead>
    <tr>
      <th>Ticker</th>
      <th>Last Price</th>
      <th>Buy Value</th>
      <th>Sell Value</th>
      <th>Volume</th>
    </tr>
    </thead>
    <tbody>
      <tr>
        <td>RCP Average</td>
        <td></td>
        <td>{{ $scrape->average }}</td>
        <td></td>
      </tr>
      @foreach ($scrape->rcp_scrape_pollsters as $scrape_pollster)
      <tr>
        <td>{{ $scrape_pollster->rcp_contest_pollster->name }}</td>
        <td>{{ $scrape_pollster->rcp_contest_poll->date_end }}</td>
        <td>{{ $scrape_pollster->rcp_contest_poll->percent_favor }}</td>
        <td><a href="{{ $scrape_pollster->rcp_contest_pollster->url_for_scraping }}">Scrape</a></td>
      </tr>
      @endforeach
    </tbody>
  </table>
@stop

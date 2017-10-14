@extends('m.base')
@section('content')
    <h1>Money Machine Dashboard</h1>
    <h5>Welcome to the Money Machine!</h5>
    <div><a href="/pi/scrape/all">Scrape PredictIt</a></div>
    <div><a href="/rcp">Scrape RCP</a></div>
    <div><a href="/pi/analyze">Analyze PredictIt Markets</a></div>
    <div><a href="/rcp/scrapes">View RCP Update History</a></div>
@stop

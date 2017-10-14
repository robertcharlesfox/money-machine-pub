<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>
        @section('title')
        Money Machine
        @show
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/all.css">
    @section('css')
    @show
</head>
<body>

<section class="navbar-section">
    <div class="navbar navbar-inverse" role="navigation">
        <div class="container">
	    	<div class="inner-container">
                <div class="navbar">
                    <ul class="nav navbar-nav">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Competitions <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li>
                                    @foreach (PiContest::where('category', '=', 'competition')->where('active', '=', 1)->get()->sortBy('name') as $competition)
                                    <a href="/admin/contests/competition/{{ $competition->id }}">{{ $competition->name }}</a>
                                    @endforeach
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Projections <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="/rcp/projections/tvc/187">RCP TvC 2-way Projections</a>
                                    <a href="/rcp/projections/tvc/188">RCP TvC 3-way Projections</a>
                                    <a href="/rcp/projections/tvc/189">RCP TvC 4-way Projections</a>
                                    <a href="/rcp/projections/approval/1">RCP Obama Projections</a>
                                    <a href="/rcp/projections/approval/8">RCP Congress Projections</a>
                                    <a href="/rcp/projections/approval/3">RCP Right Track Projections</a>
                                    <a href="/rcp/projections/approval/7">RCP ACA Projections</a>
                                    <a href="/rcp/projections/nomination/dem">RCP Dem Candidate Projections</a>
                                    <a href="/rcp/projections/nomination/gop">RCP GOP Candidate Projections</a>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Updates <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="/rcp/updates/approval/187">RCP TvC 2-way Updates</a>
                                    <a href="/rcp/updates/approval/188">RCP TvC 3-way Updates</a>
                                    <a href="/rcp/updates/approval/189">RCP TvC 4-way Updates</a>
                                    <a href="/rcp/updates/approval/1">RCP Obama Updates</a>
                                    <a href="/rcp/updates/approval/8">RCP Congress Updates</a>
                                    <a href="/rcp/updates/approval/3">RCP Right Track Updates</a>
                                    <a href="/rcp/updates/approval/7">RCP ACA Updates</a>
                                    <a href="/rcp/updates/nomination/dem">RCP Dem Candidate Updates</a>
                                    <a href="/rcp/updates/nomination/gop">RCP GOP Candidate Updates</a>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Analyze <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="/analyze/gallup">Gallup Dailies</a>
                                    <a href="/analyze/rasmussen">Rasmussen Dailies</a>
                                    <a href="/onering/bubbles">PredictIt Bubbles</a>
                                    <a href="/pi/market_depth">PredictIt Market Depth</a>
                                    <a href="/rcp/scrapes">RCP Scrapes</a>
                                    <a href="/rcp/pollsters">RCP Pollsters</a>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="/admin/contests">Manage Contests</a>
                                    <a href="/admin/questions">Manage Questions</a>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Scrape <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="/cron/rcp/scrape/30">Scrape RCP Right Track - temp</a>
                                    <a href="/cron/rcp/candidates">Scrape RCP Candidates</a>
                                    <a href="/cron/rasmussen/righttrack">Rasmussen Right Track</a>
                                    <a href="/cron/rasmussen/obama">Rasmussen Obama</a>
                                    <a href="/cron/rasmussen/candidates">Rasmussen Candidates</a>
                                    <a href="/cron/gallup/obama">Gallup Obama</a>
                                    <a href="/cron/gallup/congress">Gallup Congress</a>
                                    <a href="/cron/gallup/homepage/congress">Gallup Homepage - Congress</a>
                                    <a href="/cron/reuters/update/obama">Reuters Obama Update</a>
                                    <a href="/cron/reuters/update/righttrack">Reuters Right Track Update</a>
                                    <a href="/cron/reuters/report">Reuters Weekly Report</a>
                                    <a href="/cron/economist">Economist Weekly</a>
                                    <a href="/cron/huffpo">HuffPo Pollster</a>
                                    <a href="/cron/ibd">IBD Polls</a>
                                    <a href="/cron/marist">Marist Polls</a>
                                    <a href="/cron/bloomberg">Bloomberg Polls</a>
                                    <a href="/cron/fox/polls">FOX Polls</a>
                                    <a href="/cron/quin">Quin Polls</a>
                                    <a href="/cron/mtp">Meet The Press</a>
                                    <a href="/cron/cbs">CBS Polls</a>
                                    <a href="/cron/pew">Pew Polls</a>
                                    <a href="/cron/pewforum">Pew Forum Polls</a>
                                    <a href="/cron/ap">AP Polls</a>
                                    <a href="/cron/suffolk">USAT-Suffolk Polls</a>
                                    <a href="/cron/monmouth">Monmouth Polls</a>
                                    <a href="/cron/nationaljournal">National Journal Polls</a>
                                    <a href="/pi/scrape/all">Scrape PredictIt</a>
                                    <a href="/rcp">Scrape RCP</a>
                                    <a href="/michanikos/1">Scrape Mic Obama</a>
                                    <a href="/michanikos/2">Scrape Mic Congress</a>
                                    <a href="/michanikos/3">Scrape Mic Dem</a>
                                    <a href="/michanikos/4">Scrape Mic Gop</a>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Bot <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="/autotrade/dashboard">Auto Trade Dashboard</a>
                                    <a href="/bot/droptrades">Set Up RCP Drop Trades</a>
                                    <a href="/bot/addtrades">Set Up RCP Add Trades</a>
                                    <a href="/bot/trade/queue">Queue Up TradeBot Jobs</a>
                                    <a href="/bot/trade">Trade With A Bot</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<section>
    <div class="container">

    @if (Session::get('success'))
        <div class="alert alert-success alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{ Session::get('success') }}
        </div>
    @endif

    @if (Session::get('info'))
        <div class="alert alert-info alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{ Session::get('info') }}
        </div>
    @endif

    @if (Session::get('warning'))
        <div class="alert alert-warning alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{ Session::get('warning') }}
        </div>
    @endif

    @if (Session::get('danger'))
        <div class="alert alert-danger alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{ Session::get('danger') }}
        </div>
    @endif

    @yield('content')

</div>
</section>

<script src="/js/all.js"></script>
@section('scripts')
@show

</body>
</html>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    <title>
    	@section('title')
        | Patience Consulting
        @show
    </title>
    <!--global css starts-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/frontend/custom.css') }}">
    <!--end of global css-->
    <!--page level css-->
    @yield('header_styles')
    <!--end of page level css-->
</head>

<body>
    <!-- Header Start -->
    <header>
        <!-- Icon Section Start -->
        <div class="icon-section">
            <div class="container">
                <ul class="list-inline">
                    <li class="pull-right">
                        <ul class="list-inline icon-position">
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <!-- //Icon Section End -->
        <!-- Nav bar Start -->
        <nav class="navbar navbar-default container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#collapse">
                    <span><a href="#">_<i class="livicon" data-name="responsive-menu" data-size="25" data-loop="true" data-c="#757b87" data-hc="#ccc"></i>
                    </a></span>
                </button>
                <a class="navbar-brand" href="/autotrade/dashboard"><img src="{{ asset('assets/images/turtle.jpg') }}" alt="logo" class="logo_position" style="width:60px;height:50px;">
                </a>
            </div>
            <div class="collapse navbar-collapse" id="collapse">
                <ul class="nav navbar-nav navbar-right">
                    @if (PiContest::find(186)->auto_trade_this_contest)
                    <li><a style="color:red" href="/onering/autotrade/off">AutoTrade is ON</a></li>
                    @else
                    <li><a style="color:blue"href="/onering/autotrade/on">AutoTrade is OFF</a></li>
                    @endif
                    <li><a href="/autotrade/dashboard"> Home</a></li>
                    <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"> Projections</a>
                        <ul class="dropdown-menu" role="menu">
                            <li>
                                <a href="/rcp/projections/favorables/1">RCP Obama Projections</a>
                                <a href="/rcp/projections/favorables/8">RCP Congress Projections</a>
                                <a href="/rcp/projections/favorables/3">RCP Right Track Projections</a>
                                <a href="/rcp/projections/approval/1">RCP Obama Projections-old</a>
                                <a href="/rcp/projections/approval/8">RCP Congress Projections-old</a>
                                <a href="/rcp/projections/approval/3">RCP Right Track Projections-old</a>
                                <a href="/rcp/projections/drops">RCP Drop Analysis</a>
                                <a href="/onering/destiny">OneRing Destiny</a>
                                <a href="/onering/nazgul">OneRing Nazgul</a>
                                <a href="/onering/rcp/scrapers">OneRing RCP Scrapers</a>
                                <a href="/onering/bubbles">PredictIt Bubbles</a>
                                <a href="/pi/analyze/lines">Line Analysis</a>
                                <a href="/admin/contests">RCP Contest Admin</a>
                            </li>
                        </ul>
                    </li>
                    <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"> CvT</a>
                        <ul class="dropdown-menu" role="menu">
                            <li>
                                @foreach (PiContest::where('category', '=', 'poll_other')->where('active', '=', 1)->orderBy('rcp_scrape_frequency', 'asc')->get()->sortByDesc(function($contest){ return $contest->calculated_spread; }) as $poll_contest)
                                <a href="/rcp/projections/tvc/{{ $poll_contest->id }}">
                                    {{ $poll_contest->name }}
                                    @if ($poll_contest->last_rcp_update()->Clinton > $poll_contest->last_rcp_update()->Trump)
                                        <span style="color:blue">{{  $poll_contest->last_rcp_update()->spread }}</span>
                                    @elseif ($poll_contest->last_rcp_update()->Clinton < $poll_contest->last_rcp_update()->Trump)
                                        <span style="color:red">{{  $poll_contest->last_rcp_update()->spread }}</span>
                                    @else
                                        {{  $poll_contest->last_rcp_update()->spread }}
                                    @endif
                                </a>
                                @endforeach
                            </li>
                        </ul>
                    </li>
                    <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"> Contract Lists</a>
                        <ul class="dropdown-menu" role="menu">
                            <li>
                                <a href="/autotrade/fundraising">Fundraising</a>
                                <a href="/autotrade/polls_clinton_vs_trump">Clinton vs Trump 2-way</a>
                                <a href="/autotrade/polls_clinton_vs_trump_pa">Clinton vs Trump - PA</a>
                                <a href="/autotrade/polls_clinton_vs_trump_fl">Clinton vs Trump - FL</a>
                                <a href="/autotrade/polls_clinton_vs_trump_nv">Clinton vs Trump - NV</a>
                                <a href="/autotrade/polls_clinton_vs_trump_nc">Clinton vs Trump - NC</a>
                                <a href="/autotrade/polls_clinton_vs_trump_oh">Clinton vs Trump - OH</a>
                                <a href="/autotrade/obama">Obama Approval</a>
                                <a href="/autotrade/doc">DoC</a>
                                <a href="/autotrade/congress">Congress</a>
                                <a href="/autotrade/states/dem">States - Dem</a>
                                <a href="/autotrade/states/gop">States - GOP</a>
                                <a href="/autotrade/debates">Debates</a>
                                <a href="/autotrade/binary">Binary Events</a>
                                <a href="/autotrade/check_trades">Check Executed Trades</a>
                            </li>
                        </ul>
                    </li>
                    <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"> Election Night</a>
                        <ul class="dropdown-menu" role="menu">
                            <li>
                                <a href="/elections/potus">POTUS</a>
                                <a href="/elections/senate">Senate</a>
                                <a href="/elections/governor">Governor</a>
                                <a href="/elections/house">House</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
        <!-- Nav bar End -->
    </header>
    <!-- //Header End -->
    
    <!-- slider / breadcrumbs section -->
    @yield('top')
    <div class="breadcum">
        <div class="container">
            <ol class="breadcrumb">
            @yield('crumbs')
            </ol>
        </div>
    </div>

    <!-- This is just below the breadcrumbs -->
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="control-bar sandbox-control-bar mt10">
                    @yield('control-bar-buttons')
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    @yield('content')

    <!--global js starts-->
    <script src="{{ asset('assets/js/jquery-1.11.1.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}" type="text/javascript"></script>
    <!--livicons-->
    <script src="{{ asset('assets/vendors/livicons/minified/raphael-min.js') }}"></script>
    <script src="{{ asset('assets/vendors/livicons/minified/livicons-1.4.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/frontend/josh_frontend.js') }}"></script>
    <!--global js end-->
    <!-- begin page level js -->
    @yield('footer_scripts')
    <!-- end page level js -->
</body>

</html>

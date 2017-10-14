@extends('josh/layouts/nobottom')

{{-- Page title --}}
@section('title')
{{ $page_title }} AutoTrading
@parent
@stop

{{-- page level styles --}}
@section('header_styles')
    <!--start of page level css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/frontend/faq.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/datatables/extensions/ColReorder/css/colReorder.bootstrap.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/datatables/extensions/Scroller/css/scroller.bootstrap.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/datatables/extensions/RowReorder/css/rowReorder.bootstrap.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/datatables/extensions/TableTools/css/dataTables.tableTools.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/datatables/extensions/Responsive/css/responsive.dataTables.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/datatables/extensions/bootstrap/dataTables.bootstrap.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/pages/tables.css') }}" />
    <!--end of page level css-->
@stop

{{-- Page content --}}
@section('content')
    <!-- Container Section Start -->
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="panel-group panel-accordion faq-accordion">
                            <div id="faq">
                                @foreach ($pi_contests as $contest)
                                <div class="mix category-1 col-lg-12 panel panel-default" data-value="1">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a class="collapsed" data-toggle="collapse" data-parent="#faq" href="#contest{{ $contest->id }}">
                                                <strong class="c-gray-light">{{ $contest->name }}:</strong>
                                                {{ $contest->interest_level }}
                                                <p>{{ $contest->competition_favorites }}</p>
                                                <span class="pull-right">
                                                    <i class="glyphicon glyphicon-plus"></i>
                                                </span>
                                            </a>
                                        @if ( ! $contest->pi_questions()->count())
                                        <a href="/admin/contests/competition/{{ $contest->id }}" class="btn btn-warning">Get Players</a>
                                        @else
                                            AutoTrade
                                            @if ($contest->auto_trade_this_contest)
                                            <a href="/autotrade/contests/speed/{{ $contest->id }}/fast" class="btn btn-success">Fast</a>
                                            <a href="/autotrade/contests/speed/{{ $contest->id }}/medium" class="btn btn-warning">Medium</a>
                                            <a href="/autotrade/contests/speed/{{ $contest->id }}/slow" class="btn btn-danger">Slow</a>
                                            <a href="/autotrade/contests/deactivate/{{ $contest->id }}" class="btn btn-info">Deactivate</a>
                                            @else
                                            <a href="/autotrade/contests/activate/{{ $contest->id }}" class="btn btn-danger">Activate</a>
                                            @endif
                                        <a href="/admin/contests/competition/{{ $contest->id }}" class="btn btn-warning">Config Form</a>
                                        @endif
                                        </h4>
                                    </div>
                                    <div id="contest{{ $contest->id }}" class="panel-collapse collapse">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <a href="/pi/cancel/contest/{{ $contest->id }}" class="btn btn-default btn-xs">Cancel All Orders</a>
                                                <a href="/pi/visit/contest/{{ $contest->id }}" class="btn btn-success btn-xs">Visit All</a>
                                                <a href="/pi/trade/contest/{{ $contest->id }}" class="btn btn-danger btn-xs">Trade All</a>
                                                <a href="/pi/analyze/moflow/{{ $contest->id }}" class="btn btn-primary btn-xs">Money Flow</a>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="panel panel-primary filterable">
                                                    <div class="panel-body table-responsive">
                                                        <table class="table table-striped table-bordered" id="table1">
                                                            <thead>
                                                                <tr>
                                                                    <th>Player</th>
                                                                    <th>Mkt YES¢</th>
                                                                    <th>Mkt Last¢</th>
                                                                    <th>Mkt Spread¢</th>
                                                                    <th>My Proj¢</th>
                                                                    <th>Mkt Ratio¢</th>
                                                                    <th>Mkt Net $</th>
                                                                    <th>Mkt Ratio $</th>
                                                                    <th>Total Shares</th>
                                                                    <th>Today</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($contest->pi_questions()->orderBy('cache_market_support_yes_side_price', 'desc')->get() as $player)
                                                                @if ($player->active)
                                                                <tr>
                                                                    <td>
                                                                        @if ($contest->category == 'fundraising')
                                                                        ${{ number_format($player->fundraising_low, 2) }} - ${{ number_format($player->fundraising_high, 2) }}
                                                                        @elseif ($contest->category == 'polls_clinton_vs_trump')
                                                                        {{ number_format($player->fundraising_low, 1) }} - {{ number_format($player->fundraising_high, 1) }}
                                                                        @endif
                                                                        {{ $player->question_ticker }}
                                            <a href="/pi/visit/question/{{ $player->id }}" class="btn btn-success btn-xs">V</a>
                                            @if ($player->chance_to_win == 0)
                                            <a href="/admin/questions/deactivate/{{ $player->id }}" class="btn btn-warning btn-xs">Bye</a>
                                            @else
                                            <a href="/pi/trade/question/{{ $player->id }}" class="btn btn-danger btn-xs">T</a>
                                            <a href="/pi/cancel/question/{{ $player->id }}" class="btn btn-default btn-xs">X</a>
                                            @endif

                                                                    </td>
                                                                    <td>{{ $player->cache_market_support_yes_side_price }}</td>
                                                                    <td>{{ $player->cache_last_trade_price }}</td>
                                                                    <td>{{ $player->cache_market_support_net_price_spread }}</td>
                                                                    <td>{{ $player->chance_to_win }}</td>
                                                                    <td>{{ $player->cache_market_support_ratio_price }}</td>
                                                                    <td>{{ $player->cache_market_support_net_dollars }}</td>
                                                                    <td>{{ $player->cache_market_support_ratio_dollars }}</td>
                                                                    <td>{{ $player->cache_total_shares }}</td>
                                                                    <td>{{ $player->cache_todays_volume }}</td>
                                                                </tr>
                                                                @endif
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@stop

{{-- page level scripts --}}
@section('footer_scripts')
    <!--page level js starts-->
    <script type="text/javascript" src="{{ asset('assets/js/frontend/faq.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/vendors/mixitup/src/jquery.mixitup.js') }}"></script>

    <script type="text/javascript" src="{{ asset('assets/vendors/datatables/js/jquery.dataTables.min.js') }}" ></script>
    <script type="text/javascript" src="{{ asset('assets/vendors/datatables/extensions/TableTools/js/dataTables.tableTools.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/vendors/datatables/extensions/ColReorder/js/dataTables.colReorder.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/vendors/datatables/extensions/Scroller/js/dataTables.scroller.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/vendors/datatables/extensions/RowReorder/js/dataTables.rowReorder.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/vendors/datatables/extensions/Responsive/js/dataTables.responsive.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/vendors/datatables/extensions/bootstrap/dataTables.bootstrap.min.js') }}" ></script>
    <script type="text/javascript" src="{{ asset('assets/js/pages/table-advanced.js') }}" ></script>
    <!--page level js end-->
@stop

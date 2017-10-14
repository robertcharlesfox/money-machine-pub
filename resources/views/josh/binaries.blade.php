@extends('josh/layouts/default')

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

{{-- breadcrumb --}}
@section('top')
    <div class="breadcum">
        <div class="container">
            <ol class="breadcrumb">
                <li>
                    <a href="/autotrade/dashboard"> <i class="livicon icon3 icon4" data-name="home" data-size="18" data-loop="true" data-c="#3d3d3d" data-hc="#3d3d3d"></i>Dashboard
                    </a>
                </li>
                <li class="hidden-xs">
                    <i class="livicon icon3" data-name="angle-double-right" data-size="18" data-loop="true" data-c="#01bc8c" data-hc="#01bc8c"></i>
                    <a href="#">{{ $page_title }} AutoTrading</a>
                </li>
            </ol>
            <div class="pull-right">
                <i class="livicon icon3" data-name="question" data-size="20" data-loop="true" data-c="#3d3d3d" data-hc="#3d3d3d"></i> FAQ
            </div>
        </div>
    </div>
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
                                @foreach ($pi_questions as $question)
                                <div class="mix category-1 col-lg-12 panel panel-default" data-value="1">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a class="collapsed" data-toggle="collapse" data-parent="#faq" href="#question{{ $question->id }}">
                                                <strong class="c-gray-light">{{ $question->question_ticker }}:</strong>
                                                {{-- $question->interest_level --}}
                                                @if ($question->auto_trade_me)
                                                AutoTrade
                                                {{ $question->pi_autotrade_speed }}
                                                <a href="/autotrade/binary/speed/{{ $question->id }}/fast" class="btn btn-success">Fast</a>
                                                <a href="/autotrade/binary/speed/{{ $question->id }}/medium" class="btn btn-warning">Medium</a>
                                                <a href="/autotrade/binary/speed/{{ $question->id }}/slow" class="btn btn-danger">Slow</a>
                                                <a href="/admin/questions/deactivate/{{ $question->id }}" class="btn btn-info">Deactivate</a>
                                                @else
                                                <a href="/admin/questions/activate/{{ $question->id }}" class="btn btn-danger">Activate</a>
                                                @endif
                                                <span class="pull-right">
                                                    <i class="glyphicon glyphicon-plus"></i>
                                                </span>
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="question{{ $question->id }}" class="panel-collapse collapse">
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
                                                                @if ($question->active)
                                                                <tr>
                                                                    <td>{{ $question->question_ticker }}
                                            <a href="/pi/visit/question/{{ $question->id }}" class="btn btn-success btn-xs">V</a>
                                            @if ($question->chance_to_win == 0)
                                            <a href="/admin/questions/deactivate/{{ $question->id }}" class="btn btn-warning btn-xs">Bye</a>
                                            @else
                                            <a href="/pi/trade/question/{{ $question->id }}" class="btn btn-danger btn-xs">T</a>
                                            <a href="/pi/cancel/question/{{ $question->id }}" class="btn btn-default btn-xs">X</a>
                                            @endif

                                                                    </td>
                                                                    <td>{{ $question->cache_market_support_yes_side_price }}</td>
                                                                    <td>{{ $question->cache_last_trade_price }}</td>
                                                                    <td>{{ $question->cache_market_support_net_price_spread }}</td>
                                                                    <td>{{ $question->chance_to_win }}</td>
                                                                    <td>{{ $question->cache_market_support_ratio_price }}</td>
                                                                    <td>{{ $question->cache_market_support_net_dollars }}</td>
                                                                    <td>{{ $question->cache_market_support_ratio_dollars }}</td>
                                                                    <td>{{ $question->cache_total_shares }}</td>
                                                                    <td>{{ $question->cache_todays_volume }}</td>
                                                                </tr>
                                                                @endif
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

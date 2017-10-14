@extends('josh.layouts.nobottom')

{{-- page level styles --}}
@section('header_styles')
<style>
#section-pollster-table th 
{
    text-align:center; 
    vertical-align:middle;
}
#section-brackets-table th 
{
    text-align:center; 
}
#section-brackets-table 
{
    text-align:center; 
}
</style>

<link 
    rel="stylesheet" 
    type="text/css"
    href="{{ asset('assets/css/pages/tables.css') }}">

<link
    rel="stylesheet"
    type="text/css"
    href="{{ asset('assets/ext/jquery-ui/jquery-ui.min.css') }}">
<link
    rel="stylesheet"
    type="text/css"
    href="{{ asset('assets/ext/jquery-qtip/jquery.qtip.min.css') }}">

@stop

@section('content')
<div class="container">
  <h1>{{ $contest->name }} Projections</h1>
  <h3>{{ $contest->getLatestContest()->name }}</h3>

  <div class="well">
    <a id="pollster-results" href="#" class="btn btn-info btn-xs">Results</a>
    <a id="pollster-selectors" href="#" class="btn btn-info btn-xs">Selectors</a>
    <a id="pollster-early-results" href="#" class="btn btn-info btn-xs">Early Results</a>
    <a id="pollster-release-notes" href="#" class="btn btn-info btn-xs">Release Notes</a>
    <a id="pollster-drop-analysis" href="#" class="btn btn-info btn-xs">Drop Analysis</a>
    <a id="pollster-historic-results" href="#" class="btn btn-info btn-xs">Historic Results</a>
    <a id="contest-trade" href="/pi/trade/contest/{{ $contest->id }}" class="btn btn-danger btn-xs">Trade!</a>
    <a id="contest-visit" href="/pi/visit/contest/{{ $contest->id }}" class="btn btn-warning btn-xs">Visit</a>
    <a href="/pi/cancel/contest/{{ $contest->id }}" class="btn btn-default">Cancel All Orders</a>
  </div>

  <div class="well">
      @include ('m.rcp.tvc.inc.contest-trading-form')
  </div>

  <div class="well">
    @if ($contest->id == 1 || $contest->id == 3 || $contest->id == 8 || $contest->id == 190 || $contest->id == 191)
    <h4 align="center">Current RCP Average: <span id="rcp-current-average">{{ $last_rcp_update->percent_approval }}</span> </h4>
    @else
    <h4 align="center">Current RCP Average: <span id="rcp-current-average">{{ $last_rcp_update->spread }}</span> </h4>
    @endif
    <h4 align="center">All-Inclusive Estimate: <span id="rcp-all-inclusive">{{ $contest_values['projections']['market']['all_inclusive'] }}</span></h4>
    <h4 align="center">Un-Adjusted Estimate: <span id="rcp-un-adjusted">{{ $contest_values['projections']['straight']['all_inclusive'] }}</span></h4>

    @if ($contest->id == 189)
    {{--
    <h4>{{ 'Chance of ending at or above ' . $contest->approval_threshold_1 . ': ' . $last_rcp_update->binary_valuation($contest->approval_threshold_1) }}</h4>
    --}}
    @elseif (in_array($contest->id, $contest->bracketContests))
    <div class="table-scrollable" id="section-brackets-table">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Q ID</th>
                    <th>Lower End</th>
                    <th>Upper End</th>
                    <th>Probability</th>
                    <th>Yes ¢ ({{ $contest->competition_yes_total }})</th>
                    <th>No ¢ ({{ $contest->competition_no_total }})</th>
                    <th>Net $</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($contest_values['questions'] as $question)
                <tr>
                    <td>{{ $question->id }}</td>
                    <td>{{ $question->fundraising_low }}</td>
                    <td>{{ $question->fundraising_high }}</td>
                    <td><span id="polling-contract-{{ $question->id }}">{{ $question->chance_to_win }}</span></td>
                    <td>{{ $question->cache_market_support_yes_side_price }}</td>
                    <td>{{ $question->cache_market_support_no_side_price }}</td>
                    <td>{{ $question->cache_market_support_net_dollars }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
  </div>
</div>

<!-- Brackets, evaluations, current prices -->
<div class="container">
    <div class="row" id="section-pollster-table">
        <div class="col-md-12">
            <div class="table-scrollable">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th class="pollster-dates-values">Dates</th>
                            @foreach ($candidate_names as $name)
                              <th class="analysis-table-results">{{ $name }}</th>
                            @endforeach
                            @foreach ($candidate_names as $name)
                              <th class="analysis-table-early-results" style="display:none;">Early {{ $name }}</th>
                            @endforeach
                            <th class="analysis-table-selectors" style="display:none;">% Update</th>
                            <th class="analysis-table-selectors" style="display:none;">% Add</th>
                            <th class="analysis-table-selectors" style="display:none;">Proj.</th>
                            <th class="analysis-table-drop-analysis" style="display:none;">% Drop</th>
                            <th class="analysis-table-drop-analysis" style="display:none;">Age</th>
                            <th class="analysis-table-release-notes" style="display:none;">Mark Old</th>
                            <th class="analysis-table-release-notes" style="display:none;">Frequency</th>
                            <th class="pollster-dates-values">Diff/Weight</th>
                            <th>Save</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($contest_values['pollsters']['rcp_average'] as $pollster)
                          @include ('m.rcp.tvc.inc.pollsters-projection')
                        @endforeach
                        <tr>
                            <th>Other Pollsters</th>
                            <th class="pollster-dates-values">Dates</th>
                            @foreach ($candidate_names as $name)
                              <th class="analysis-table-results">{{ $name }}</th>
                            @endforeach
                            @foreach ($candidate_names as $name)
                              <th class="analysis-table-early-results" style="display:none;">Early {{ $name }}</th>
                            @endforeach
                            <th class="analysis-table-selectors" style="display:none;">% Update</th>
                            <th class="analysis-table-selectors" style="display:none;">% Add</th>
                            <th class="analysis-table-selectors" style="display:none;">Proj.</th>
                            <th class="analysis-table-drop-analysis" style="display:none;">% Drop</th>
                            <th class="analysis-table-drop-analysis" style="display:none;">Age</th>
                            <th class="analysis-table-release-notes" style="display:none;">Mark Old</th>
                            <th class="analysis-table-release-notes" style="display:none;">Frequency</th>
                            <th class="pollster-dates-values">Diff/Weight</th>
                            <th>Save</th>
                        </tr>
                        @foreach ($contest_values['pollsters']['others'] as $pollster)
                          @include ('m.rcp.tvc.inc.pollsters-other')
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@stop

{{-- footer scripts --}}
@section('footer_scripts')
    <!--EA page level js starts-->
    <script
        type="text/javascript"
        src="{{ asset('assets/ext/jquery/jquery.min.js') }}"></script>
    <script
        type="text/javascript"
        src="{{ asset('assets/ext/jquery-ui/jquery-ui.min.js') }}"></script>
    <script
        type="text/javascript"
        src="{{ asset('assets/ext/jquery-qtip/jquery.qtip.min.js') }}"></script>
    <script
        type="text/javascript"
        src="{{ asset('assets/ext/datejs/date.js') }}"></script>
    
    <script
        type="text/javascript"
        src="{{ asset('js/custom/tvc_projections.js') }}"></script>

    <script
        type="text/javascript"
        src="{{ asset('js/ea/general_functions.js') }}"></script>

    <script type="text/javascript">
        var GlobalVariables = {
            baseUrl             : <?php echo '""'; ?>,
            csrfToken           : <?php echo json_encode($ajax_token); ?>
        };

        $(document).ready(function() {
            TvCProjections.initialize(true);
        });
    </script>

    <!--EA page level js ends-->
@stop

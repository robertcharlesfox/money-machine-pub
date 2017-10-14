@extends('m.base')
@section('content')
  <h1>{{ $contest->name }} Projections</h1>
  <h3>Current RCP Average: {{ $last_update->percent_approval }}</h3>
  <h3>Exclude likely drops: {{ $last_update->avgMinusLikelyDropouts() }}</h3>
  <h3>Final Poll #'s ONLY: {{ $last_update->avgWithFridaysFinals() }}</h3>
  <h3>After Random Poll(s)</h3>
  <form id="contest-{{ $contest->id }}" action="/admin/contests/random" method="post">
      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <input type="hidden" name="_method" value="POST">
      <input type="hidden" name="contest_id" value="{{ $contest->id }}">
        <div class="input-group input-group-sm">
        <input type="number" name="random_polls_to_add" value="{{ $contest->random_polls_to_add }}" class="form-control" />
      </div>
  </form>
  <h3>All-Inclusive Estimate: {{ $last_update->avgWithFinalsAndProjections(true, '', '', false, true) }}</h3>
  @if ($contest->id == 1 && count($obama_values) == 5)
    <h4>Chance of ending at or above {{ $obama_values[0] }}: {{ $last_update->valuation($obama_values[0], 99.9) }}</h4>
    <h4>Chance of ending between {{ $obama_values[1] . '-' . $obama_values[0] }}: {{ $last_update->valuation($obama_values[1], $obama_values[0]) }}</h4>
    <h4>Chance of ending between {{ $obama_values[2] . '-' . $obama_values[1] }}: {{ $last_update->valuation($obama_values[2], $obama_values[1]) }}</h4>
    <h4>Chance of ending between {{ $obama_values[3] . '-' . $obama_values[2] }}: {{ $last_update->valuation($obama_values[3], $obama_values[2]) }}</h4>
    <h4>Chance of ending below {{ $obama_values[4] }}: {{ $last_update->valuation(0.1, $obama_values[4]) }}</h4>
  @else
    <h4>{{ 'Chance of ending at or above ' . $contest->approval_threshold_1 . ': ' . $last_update->valuation($contest->approval_threshold_1) }}</h4>
  @endif

  @foreach ($last_update->rcp_contest_pollsters_for_projections() as $pollster)
    @if ($pollster->is_likely_final_for_week)
    <div class="panel panel-success">
    @elseif ($pollster->is_likely_dropout)
    <div class="panel panel-danger">
    @elseif ($pollster->un_included_actual_result > 1)
    <div class="panel panel-info">
    @elseif ($pollster->projected_result > 1)
    <div class="panel panel-warning">
    @else
    <div class="panel panel-default">
    @endif
      <div class="panel-heading" role="tab" id="heading{{ $pollster->id }}">
        <div class="row">
          <h4 class="panel-title">
            <div class="col-md-12">
              <a class="collapsed" role="button" data-toggle="collapse" href="#collapse{{ $pollster->id }}" aria-expanded="false" aria-controls="collapse{{ $pollster->id }}">
                {{ $pollster->name }}
                {{ (int) $pollster->avgInclusionValue() }}%
                @if ( ! $pollster->is_likely_final_for_week)
                  @if ($pollster->un_included_actual_result > 0)
                    Un-included Actual Result! {{ $pollster->un_included_actual_result }}
                  @elseif ($pollster->projected_result > 0)
                    Projection: {{ $pollster->projected_result }}
                  @elseif ($pollster->id == ID_OBAMA_GALLUP)
                    Forecast: {{ $pollster->gallupProjAvg() }} ± {{ $pollster->gallupStDev() }}
                  @elseif ($pollster->id == ID_OBAMA_RASMUSSEN)
                    Forecast: {{ $pollster->rasmussenProjAvg() }} ± {{ $pollster->rasmussenStDev() }}
                  @elseif ( ! $pollster->is_likely_dropout)
                    Forecast: {{ $pollster->trendForecast($recent_polls) }} ± {{ $pollster->trendStDev($recent_polls) }}
                  @endif
                @else
                FINAL
                @endif
              </a>
              {{ $pollster->latest_poll()->date_end }}
              {{ $pollster->latest_poll()->current_poll_age }} days old, {{ $pollster->latest_poll()->current_days_in }} in
            </div>
          </h4>
        </div>
        <div class="row">
            <div class="col-md-12">
              @include('m.admin.contest_pollsters.panel-header-form-approval')
            </div>
        </div>
      </div>
      <div id="collapse{{ $pollster->id }}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading{{ $pollster->id }}">
        <div class="panel-body">
          @if ($pollster->update_frequency != '1daily')
          <div class="row">
            <h5>
              <div class="col-md-2">Poll Date Range</div>
              <div class="col-md-1">% Favor</div>
              <div class="col-md-3">Day Added</div>
              <div class="col-md-3">Day Dropped</div>
              <div class="col-md-2">Final Age/Time In</div>
            </h5>
          </div>
          @foreach ($pollster->sorted_polls() as $poll)
          <div class="row">
            <div class="col-md-2">{{ $poll->date_start . ' - ' . $poll->date_end }}</div>
            <div class="col-md-1">{{ (int) $poll->percent_favor }}</div>
            <div class="col-md-3">{{ $poll->rcp_update_add ? $poll->last_add->rcp_update->local_rcp_timestamp() : '' }}</div>
            <div class="col-md-3">{{ $poll->rcp_update_drop ? $poll->last_drop->rcp_update->local_rcp_timestamp() : '' }}</div>
            <div class="col-md-2">{{ $poll->age_of_poll_when_dropped_from_rcp . ': ' . $poll->length_in_average }}</div>
          </div>
          @endforeach
          @endif
        </div>
      </div>
    </div>
  @endforeach

  <h3>Other Pollsters:</h3>
  @foreach ($other_pollsters as $pollster)
    @if ($pollster->showAsOtherPollster($last_update))
    <div class="panel panel-default">
      <div class="panel-heading" role="tab" id="pollster-heading{{ $pollster->id }}">
        <div class="row">
          <h4 class="panel-title">
            <div class="col-md-12">
              <a class="collapsed" role="button" data-toggle="collapse" href="#pollster-collapse{{ $pollster->id }}" aria-expanded="false" aria-controls="pollster-collapse{{ $pollster->id }}">
                {{ $pollster->name }}
                Last: {{ (int) $pollster->latest_poll()->percent_favor }}%
                Forecast: {{ $pollster->trendForecast($recent_polls) }} ± {{ $pollster->trendStDev($recent_polls) }}
              </a>
            </div>
          </h4>
        </div>
        <div class="row">
          <div class="col-md-12">
            @include('m.admin.contest_pollsters.panel-header-form-approval-others')
          </div>
        </div>
      </div>
      <div id="pollster-collapse{{ $pollster->id }}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="pollster-heading{{ $pollster->id }}">
        <div class="panel-body">
          <div class="row">
            <h5>
              <div class="col-md-2">Poll Date Range</div>
              <div class="col-md-1">% Favor</div>
              <div class="col-md-3">Day Added</div>
              <div class="col-md-3">Day Dropped</div>
              <div class="col-md-2">Final Age/Time In</div>
            </h5>
          </div>
          @foreach ($pollster->sorted_polls() as $poll)
          <div class="row">
            <div class="col-md-2">{{ $poll->date_start . ' - ' . $poll->date_end }}</div>
            <div class="col-md-1">{{ (int) $poll->percent_favor }}</div>
            <div class="col-md-3">{{ $poll->rcp_update_add ? $poll->last_add->rcp_update->local_rcp_timestamp() : '' }}</div>
            <div class="col-md-3">{{ $poll->rcp_update_drop ? $poll->last_drop->rcp_update->local_rcp_timestamp() : '' }}</div>
            <div class="col-md-2">{{ $poll->age_of_poll_when_dropped_from_rcp . ': ' . $poll->length_in_average }}</div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
    @endif
  @endforeach
@stop

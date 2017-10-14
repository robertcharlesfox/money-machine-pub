@extends('m.base')
@section('content')
  <h1>{{ $contest->name }} Projections</h1>
  @if ($include_debate)
  <div class="row">
    <div class="col-md-4">
      <h3>Current Leader</h3>
      @foreach ($debate_winners as $candidate)
      <h5>
        {{ $candidate['name'] . ': ' . $candidate['text'] }}
      </h5>
      @endforeach
    </div>
    <div class="col-md-4">
      <h3>Current Loser</h3>
      @foreach ($debate_losers as $candidate)
      <h5>
        {{ $candidate['name'] . ': ' . $candidate['text'] }}
      </h5>
      @endforeach
    </div>
    <div class="col-md-4">
      <h3>Random Poll Impact</h3>
      @foreach ($random_poll_impact as $candidate)
      <h5>
        {{ $candidate['name'] . ': ' . $candidate['text'] }}
      </h5>
      @endforeach
    </div>
  </div>
  <div class="row">
    <div class="col-md-4">
      <h3>After Random Poll(s)</h3>
      <form id="contest-{{ $contest->id }}" action="/admin/contests/random" method="post">
          <input type="hidden" name="_token" value="{{ csrf_token() }}">
          <input type="hidden" name="_method" value="POST">
          <input type="hidden" name="contest_id" value="{{ $contest->id }}">
            <div class="input-group input-group-sm">
            <input type="number" name="random_polls_to_add" value="{{ $contest->random_polls_to_add }}" class="form-control" />
          </div>
      </form>
    </div>
    <div class="col-md-4">
    </div>
    <div class="col-md-4">
    </div>
  </div>
  <div class="row">
    <div class="col-md-4">
      <h3>Projected Leader</h3>
      @foreach ($debate_winners_projected as $candidate)
      <h5>
        {{ $candidate['name'] . ': ' . $candidate['text'] }}
      </h5>
      @endforeach
    </div>
    <div class="col-md-4">
      <h3>Projected Loser</h3>
      @foreach ($debate_losers_projected as $candidate)
      <h5>
        {{ $candidate['name'] . ': ' . $candidate['text'] }}
      </h5>
      @endforeach
    </div>
    <div class="col-md-4">
      <h3>Starting Values</h3>
      @foreach ($candidate_update->debate_candidates as $candidate => $threshold)
      <h5>
        {{ $candidate . ': ' . $threshold }}
      </h5>
      @endforeach
    </div>
  </div>
  @else
  <a href="{{ Request::url() }}/debate" class="btn btn-success">Show Debate #'s</a>
  @endif

  <h3>Current RCP Average: </h3>
  <h5>
    @foreach ($candidate_update->candidates as $candidate)
      {{ $candidate . ': ' . $rcp_update->$candidate }}
    @endforeach
  </h5>
  <h3>Exclude likely drops: </h3>
  <h5>
    @foreach ($candidate_update->candidates as $candidate)
      {{ $candidate . ': ' . $rcp_update->avgMinusLikelyDropouts($candidate) }}
    @endforeach
  </h5>
  <h3>Final Poll #'s ONLY: </h3>
  <h5>
    @foreach ($candidate_update->candidates as $candidate)
      {{ $candidate . ': ' . $rcp_update->avgWithFridaysFinals($candidate) }}
    @endforeach
  </h5>
  <h3>All-Inclusive Estimate: </h3>
  @foreach ($candidate_update->contest_candidates as $candidate => $threshold)
  <h5>
    {{ $candidate . ': ' . $rcp_update->avgWithFinalsAndProjections(true, $candidate) }}
    Chance of ending at or above {{ $threshold . ': ' . $rcp_update->valuation($threshold, '', $candidate) }}
  </h5>
  @endforeach

  @if ($include_debate)
  <h3>Debate Pollsters</h3>
  @foreach ($debate_pollsters as $pollster)
    <div class="panel panel-warning">
      <div class="panel-heading" role="tab">
        <h4 class="panel-title">
          <div class="row">
            <div class="col-md-12">
                {{ $pollster->name }}
                {{ $pollster->latest_poll()->date_end }}
            </div>
          </div>
        </h4>
        <div class="row">
          <h5>
            <div class="col-md-10 col-md-offset-1">
              @foreach ($candidate_update->debate_candidates as $candidate => $threshold)
                {{ $candidate . ': ' . $pollster->latest_poll()->$candidate }}
              @endforeach
            </div>
          </h5>
        </div>
      </div>
    </div>
  @endforeach
  @endif

  <h3>All Pollsters</h3>
  @foreach ($projection_pollsters as $pollster)
    @if ($pollster->is_likely_final_for_week)
    <div class="panel panel-success">
    @elseif ($pollster->is_likely_dropout)
    <div class="panel panel-danger">
    @elseif ($pollster->un_included_actual_result > 1)
    <div class="panel panel-info">
    @else
    <div class="panel panel-default">
    @endif
      <div class="panel-heading" role="tab" id="heading{{ $pollster->id }}">
        <div class="row">
          <h4 class="panel-title">
            <div class="col-md-4">
              <a class="collapsed" role="button" data-toggle="collapse" href="#collapse{{ $pollster->id }}" aria-expanded="false" aria-controls="collapse{{ $pollster->id }}">
                {{ $pollster->name }}
                {{ $pollster->latest_poll()->date_end }}
                {{ $pollster->latest_poll()->current_poll_age }} days old, {{ $pollster->latest_poll()->current_days_in }} in
              </a>
            </div>
          </h4>
          <h5>
            <div class="col-md-6">
              @foreach ($candidate_update->main_candidates as $candidate)
                @if ($pollster->is_likely_addition)
                  {{ $candidate . ': ' . $pollster->trendForecast($recent_polls, $candidate) }} ± {{ $pollster->trendStDev($recent_polls, $candidate) }}
                @else
                  {{ $candidate . ': ' . $pollster->latest_poll()->$candidate }}
                @endif
              @endforeach
            </div>
            <div class="col-md-2">
              @include('m.admin.contest_pollsters.panel-header-form-candidate-result')
            </div>
          </h5>
        </div>
        <div class="row">
          <h5>
            <div class="col-md-12">
              @include('m.admin.contest_pollsters.panel-header-form-candidate')
            </div>
          </h5>
        </div>
      </div>
      <div id="collapse{{ $pollster->id }}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading{{ $pollster->id }}">
        <div class="panel-body">
          <div class="row">
            <h5>
              <div class="col-md-2">Poll Date Range</div>
              <div class="col-md-3">Day Added</div>
              <div class="col-md-3">Day Dropped</div>
              <div class="col-md-2">Final Age/Time In</div>
            </h5>
          </div>
          @foreach ($pollster->rcp_contest_polls()->orderBy('date_end', 'desc')->get() as $poll)
          <div class="row">
            <h5>
              <div class="col-md-2">{{ $poll->date_start . ' - ' . $poll->date_end }}</div>
              <div class="col-md-3">{{ $poll->rcp_update_add ? $poll->last_add->rcp_update->local_rcp_timestamp() : '' }}</div>
              <div class="col-md-3">{{ $poll->rcp_update_drop ? $poll->last_drop->rcp_update->local_rcp_timestamp() : '' }}</div>
              <div class="col-md-2">{{ $poll->age_of_poll_when_dropped_from_rcp . ' / ' . $poll->length_in_average }}</div>
            </h5>
          </div>
          <div class="row">
            <h5>
              <div class="col-md-10 col-md-offset-1">
                @foreach ($candidate_update->candidates as $candidate)
                  {{ $candidate . ': ' . $poll->$candidate }}
                @endforeach
              </div>
            </h5>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  @endforeach

  <h3>Other Pollsters:</h3>
  @foreach ($other_pollsters as $pollster)
    @if ($pollster->showAsOtherPollster($rcp_update))
    <div class="panel panel-default">
      <div class="panel-heading" role="tab" id="pollster-heading{{ $pollster->id }}">
        <div class="row">
          <h4 class="panel-title">
            <div class="col-md-4">
              <a class="collapsed" role="button" data-toggle="collapse" href="#pollster-collapse{{ $pollster->id }}" aria-expanded="false" aria-controls="pollster-collapse{{ $pollster->id }}">
                {{ $pollster->name }}
                {{ $pollster->latest_poll()->date_end }}
                ({{ $pollster->latest_poll()->current_poll_age }} days old)
              </a>
            </div>
          </h4>
          <h5>
            <div class="col-md-6">
              Forecast: 
              @foreach ($candidate_update->main_candidates as $candidate)
                {{ $candidate . ': ' . $pollster->trendForecast($recent_polls, $candidate) }} ± {{ $pollster->trendStDev($recent_polls, $candidate) }}
              @endforeach
            </div>
            <div class="col-md-2">
            </div>
          </h5>
        </div>
        <div class="row">
          <h5>
            <div class="col-md-12">
              @include('m.admin.contest_pollsters.panel-header-form-candidate')
            </div>
          </h5>
        </div>
      </div>
      <div id="pollster-collapse{{ $pollster->id }}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="pollster-heading{{ $pollster->id }}">
        <div class="panel-body">
          <div class="row">
            <h5>
              <div class="col-md-2">Poll Date Range</div>
              <div class="col-md-3">Day Added</div>
              <div class="col-md-3">Day Dropped</div>
              <div class="col-md-2">Final Age/Time In</div>
            </h5>
          </div>
          @foreach ($pollster->rcp_contest_polls()->orderBy('date_end', 'desc')->get() as $poll)
          <div class="row">
            <h5>
              <div class="col-md-2">{{ $poll->date_start . ' - ' . $poll->date_end }}</div>
              <div class="col-md-3">{{ $poll->rcp_update_add ? $poll->last_add->rcp_update->local_rcp_timestamp() : '' }}</div>
              <div class="col-md-3">{{ $poll->rcp_update_drop ? $poll->last_drop->rcp_update->local_rcp_timestamp() : '' }}</div>
              <div class="col-md-2">{{ $poll->age_of_poll_when_dropped_from_rcp . ' / ' . $poll->length_in_average }}</div>
            </h5>
          </div>
          <div class="row">
            <h5>
              <div class="col-md-10 col-md-offset-1">
                @foreach ($candidate_update->candidates as $candidate)
                  {{ $candidate . ': ' . $poll->$candidate }}
                @endforeach
              </div>
            </h5>
          </div>
          @endforeach
        </div>
      </div>
    </div>
    @endif
  @endforeach
@stop

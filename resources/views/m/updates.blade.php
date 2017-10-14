@extends('m.base')
@section('content')
  <h2>RCP Updates</h2>
  <h3>{{ $contest->name }}</h3>
  <div class="row">
    <strong>
    <div class="col-md-2">Update Timestamp</div>
    <div class="col-md-2">RCP Average</div>
    <div class="col-md-2">Update # Today</div>
    <div class="col-md-6">Update Text</div>
    </strong>
  </div>
  @foreach ($rcp_days as $day)
    @if ($day->contestUpdates($contest->id)->count())
      @if (date('l', strtotime($day->rcp_date)) == 'Friday')
      <div class="panel panel-primary">
      @else
      <div class="panel panel-default">
      @endif
        <div class="panel-heading" role="tab" id="heading{{ $day->id }}">
          <h4 class="panel-title">
            <a class="collapsed" role="button" data-toggle="collapse" href="#collapse{{ $day->id }}" aria-expanded="false" aria-controls="collapse{{ $day->id }}">
              {{ $day->rcp_date }}
              {{ $day->updateSummary($contest->id) }}
            </a>
          </h4>
        </div>
        <div id="collapse{{ $day->id }}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading{{ $day->id }}">
          <div class="panel-body">
            @foreach ($day->contestUpdates($contest->id) as $update)
              <h5>{{ $update->updateSummary() }}</h5>
              @foreach ($update->rcp_update_adds as $add)
                <h5>Added {{ $add->rcp_contest_pollster->name }}</h5>
              @endforeach
              @foreach ($update->rcp_update_drops as $drop)
                <h5>Dropped {{ $drop->rcp_contest_pollster->name . ' (' . $drop->rcp_contest_poll->age_of_poll_when_dropped_from_rcp . ' days old)' }}</h5>
              @endforeach
            @endforeach
          </div>
        </div>
      </div>
    @endif
  @endforeach
@stop

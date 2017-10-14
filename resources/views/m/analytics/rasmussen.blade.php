@extends('m.base')
@section('content')
  <h3>Rasmussen Daily Analytics </h3>
  <div class="row">
    <h5>
      <div class="col-md-1">END DATE</div>
      <div class="col-md-2">Result/Calc</div>
      <div class="col-md-2">Daily Estimate</div>
      <div class="col-md-1"></div>
      <div class="col-md-3">Min/Max to fit, 2-day preview</div>
    </h5>
  </div>

  @for ($i=0 ; $i < 25 ; $i++)
    @if ($dailies[$i]->rasmussen_daily_estimate > 0)
    <div class="panel panel-info">
    @else
    <div class="panel panel-default">
    @endif
      <div class="panel-heading" role="tab">
        <h4 class="panel-title">
          <div class="row">
            <form id="daily-{{ $dailies[$i]->id }}" action="/analyze/rasmussen" method="post">
              <input type="hidden" name="_token" value="{{ csrf_token() }}">
              <input type="hidden" name="_method" value="POST">
              <input type="hidden" name="poll_id" value="{{ $dailies[$i]->id }}">
              <div class="col-md-1">{{ date('m/d', strtotime($dailies[$i]->date_end)) }}</div>
              <div class="col-md-2">
                {{ (int) $dailies[$i]->percent_favor }}
                /
                {{ round($dailies[$i]->gallupThreeDayAverage($dailies[$i+1], $dailies[$i+2])) }}
                /
                {{ round($dailies[$i]->gallupThreeDayAverage($dailies[$i+1], $dailies[$i+2]), 2) }}
              </div>
              <div class="col-md-2">
                <input type="text" name="rasmussen_daily_estimate" value="{{ $dailies[$i]->rasmussen_daily_estimate }}" class="form-control" />
              </div>
              <div class="col-md-1">
                <button type="submit" class="btn btn-warning">Save</button>
              </div>
              <div class="col-md-3">
                {{ round($dailies[$i]->gallupMinMaxToFit($dailies[$i+1], $dailies[$i+2]), 1) }}
                /
                {{ round($dailies[$i]->gallupMinMaxToFit($dailies[$i+1], $dailies[$i+2], 'max'), 1) }}
                /
                {{ round($dailies[$i]->gallupTwoDayPreview($dailies[$i+1]), 1) }}
              </div>
            </form>
          </div>
        </h4>
      </div>
    </div>
  @endfor
@stop

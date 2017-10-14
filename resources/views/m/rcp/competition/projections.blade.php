@extends('m.base')
@section('content')
  <div class="panel panel-primary">
    <div class="panel-heading">
      @include('m.rcp.competition.contest-trading-form')
    </div>
  </div>

  @foreach ($contest->pi_questions->sortByDesc('chance_to_win') as $question)
    <div class="panel panel-info">
      <div class="panel-heading" role="tab" id="heading{{ $question->id }}">
        <h4 class="panel-title">
          @include('m.rcp.competition.panel-header-form-question')
        </h4>
      </div>
      @if ($question->chance_to_win > 0)
      <div id="collapse{{ $question->id }}" class="panel-collapse" role="tabpanel" aria-labelledby="heading{{ $question->id }}">
      @else
      <div id="collapse{{ $question->id }}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading{{ $question->id }}">
      @endif
        <div class="panel-body">
          <div class="row">
            <h5>
              <div class="col-md-2">Timestamp</div>
              <div class="col-md-1">Last $</div>
              <div class="col-md-5">Bids</div>
              <div class="col-md-4">Shares</div>
            </h5>
          </div>
          @if (false)
          @foreach ($question->pi_markets->sortByDesc('created_at')->take(5) as $market)
          <div class="row">
            <div class="col-md-2">
              {{ $market->time_created }}
            </div>
            <div class="col-md-1">
              {{ $market->last_price }}
            </div>
            <div class="col-md-5">
              {{ $market->marketValues() }}
            </div>
            <div class="col-md-4">
              Total: {{ number_format($market->shares_traded, 0) }}
              Today: {{ number_format($market->todays_volume, 0) }}
              Out: {{ number_format($market->total_shares, 0) }}
            </div>
          </div>
          <div class="row">
            <div class="col-md-2">
            </div>
            <div class="col-md-1">
            </div>
            <div class="col-md-5">
              {{ $market->marketValues('prices') }}
            </div>
            <div class="col-md-4">
            </div>
          </div>
          @endforeach
          @endif
        </div>
      </div>
    </div>
  @endforeach
@stop
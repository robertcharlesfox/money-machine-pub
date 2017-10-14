@extends('m.base')
@section('content')

  <div class="panel panel-primary">
    <div class="panel-heading">
      @include('m.rcp.addtrades.form-addtrade-definition')
    </div>
  </div>

  @foreach ($add_trades as $trade)
    <div class="panel panel-info">
      <div class="panel-heading" role="tab" id="heading{{ $trade->id }}">
        <div class="row">
          <h4 class="panel-title">
            {{ substr($trade->pi_contest->name, 0, 6) }} Average: add
            {{ $trade->rcp_contest_pollster_name() }} @ {{ $trade->poll_result }}
          </h4>
        </div>
        <div class="row">
          <div class="col-md-3 col-md-offset-1">
            <h5>
              trade {{ $trade->pi_question->question_ticker }}
            </h5>
          </div>

          <div class="col-md-8">
            @include('m.rcp.addtrades.form-question-values')
          </div>
        </div>
      </div>
    </div>
  @endforeach
@stop

@section('scripts')
  <script type="text/javascript">
    $('#pi_contest_id').change(function () {
      $('#poll-result').removeClass('hidden');
      
      $('#select-question').removeClass('hidden');
      $('#pi_question_id').val('');
      $('.option-question').show();
      $(".option-question[data-question-contest-id!='" + $('#pi_contest_id').val() + "']").hide();

      $('#select-pollster').removeClass('hidden');
      $('#rcp_contest_pollster_id').val('');
      $('.option-pollster').show();
      $(".option-pollster[data-pollster-contest-id!='" + $('#pi_contest_id').val() + "']").hide();
    });

    $('#rcp_contest_pollster_id').change(function () {
      $('#button-save').removeClass('disabled');
    });
  </script>
@stop
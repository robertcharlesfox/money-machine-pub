@extends('m.base')
@section('content')

  <div class="panel panel-primary">
    <div class="panel-heading">
      @include('m.rcp.droptrades.form-droptrade-definition')
    </div>
  </div>

  @foreach ($drop_trades as $trade)
    <div class="panel panel-info">
      <div class="panel-heading" role="tab" id="heading{{ $trade->id }}">
        <div class="row">
          <h4 class="panel-title">
            {{ substr($trade->pi_contest->name, 0, 6) }} Average: drop
            {{ $trade->rcp_contest_pollster_1_name() }}
            {{ $trade->rcp_contest_pollster_2_name() }}
            {{ $trade->rcp_contest_pollster_3_name() }}
            {{ $trade->rcp_contest_pollster_4_name() }}
          </h4>
        </div>
        <div class="row">
          <div class="col-md-3 col-md-offset-1">
            <h5>
              trade {{ $trade->pi_question->question_ticker }}
            </h5>
          </div>

          <div class="col-md-8">
            @include('m.rcp.droptrades.form-question-values')
          </div>
        </div>
      </div>
    </div>
  @endforeach
@stop

@section('scripts')
  <script type="text/javascript">
    $('#pi_contest_id').change(function () {
      $('#check-autotrade').removeClass('hidden');
      
      $('#select-question').removeClass('hidden');
      $('#pi_question_id').val('');
      $('.option-question').show();

      $('#select-pollster-1').removeClass('hidden');
      $('#select-pollster-2').removeClass('hidden');
      $('#select-pollster-3').removeClass('hidden');
      $('#select-pollster-4').removeClass('hidden');
      $('#rcp_contest_pollster_id_1').val('');
      $('#rcp_contest_pollster_id_2').val('');
      $('#rcp_contest_pollster_id_3').val('');
      $('#rcp_contest_pollster_id_4').val('');
      $('.option-pollster').show();
      $(".option-pollster[data-pollster-contest-id!='" + $('#pi_contest_id').val() + "']").hide();
    });

    $('#rcp_contest_pollster_id_1').change(function () {
      $('#button-save').removeClass('disabled');
    });
  </script>
@stop
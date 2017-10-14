@extends('josh/layouts/nobottom')

@section('title')
One Ring Nazgul
@parent
@stop

@section('content')
<div class="container">
  <h3>Nazgul Entry</h3>
  <div class="well col-md-9">
      @include('josh.onering.inc-nazgul-entry-form')
  </div>
</div>

<div class="container">
  @foreach ($nazguls as $nazgul)
  <div class="panel panel-info">
    <div class="panel-heading" role="tab" id="heading{{ $nazgul->id }}">
      <div class="row">
        <h4 class="panel-title">
          {{ $nazgul->id . ': ' . $nazgul->pi_question->question_ticker . ': ' . $nazgul->buy_or_sell . ' ' . $nazgul->yes_or_no }}
          <a class="btn btn-xs btn-warning" href="/onering/nazgul/status/{{ $nazgul->id }}/deactivate" >Deactivate</a>
        </h4>
      </div>
      <div class="row">
        <div class="col-md-12">
          @include('josh.onering.inc-nazgul-update-form')
        </div>
      </div>
    </div>
  </div>
  @endforeach

  <h3>Inactive Nazguls</h3>
  @foreach ($inactive_nazguls as $nazgul)
  <div class="panel panel-info">
    <div class="panel-heading" role="tab" id="heading{{ $nazgul->id }}">
      <div class="row">
        <h4 class="panel-title">
          {{ $nazgul->id . ': ' . $nazgul->pi_question->question_ticker . ': ' . $nazgul->buy_or_sell . ' ' . $nazgul->yes_or_no }}
          <a class="btn btn-xs btn-warning" href="/onering/nazgul/status/{{ $nazgul->id }}/activate" >Activate</a>
        </h4>
      </div>
    </div>
  </div>
  @endforeach
</div>
@stop

@section('footer_scripts')
  <script type="text/javascript">
    $('#pi_contest_id').change(function () {
      $('#select-question').removeClass('hidden');
      $('#pi_question_id').val('');
      $('.option-question').show();
      $(".option-question[data-question-contest-id!='" + $('#pi_contest_id').val() + "']").hide();
    });

    $('#pi_question_id').change(function () {
      $('#button-save').removeClass('disabled');
    });
  </script>
@stop
@extends('m.base')

@section('content')

<h1>PredictIt Questions</h1>

<div class="panel panel-default">
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="questions">
                <thead>
                    <tr class="nodrag">
                        <th>Contest Name</th>
                        <th>Question Ticker</th>
                        <th>Close Date</th>
                        <th>Auto-Trade Prices</th>
                        <th width="1%">
                            <a href="/admin/questions/create" class="btn btn-primary btn-xs">Add</a>
                        </th>
                        <th width="1%">
                            <a href="/admin/questions/inactive" class="btn btn-primary btn-xs">See Old</a>
                        </th>
                        <th width="1%">
                            <a href="/admin/questions" class="btn btn-primary btn-xs">See New</a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @if ($pi_questions->count())
                        @foreach ($pi_questions as $pi_question)
                            <tr id="{{{ $pi_question->id }}}">
                                <td>{{ $pi_question->pi_contest->name }}</td>
                                <td>{{ $pi_question->question_ticker }}</td>
                                <td>{{ $pi_question->date_close }}</td>
                                <td>{{ $pi_question->autotrade }}</td>
                                <td></td>
                                <td nowrap="nowrap" align="right">
                                    <a href="/admin/questions/edit/{{ $pi_question->id }}" class="btn btn-primary btn-xs">Edit</a>
                                </td>
                                <td nowrap="nowrap" align="right">
                                    @if ($pi_question->active)
                                    <a href="/admin/questions/deactivate/{{ $pi_question->id }}" class="btn btn-primary btn-xs">Deactivate</a>
                                    @else
                                    <a href="/admin/questions/activate/{{ $pi_question->id }}" class="btn btn-primary btn-xs">Re-activate</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4">No PredictIt Questions found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

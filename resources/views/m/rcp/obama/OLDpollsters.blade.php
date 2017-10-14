@extends('m.base')
@section('content')
  <h2>RCP Pollsters</h2>
  @foreach ($rcp_contests as $contest)
    <h4>{{ $contest->name }}</h4>
    <table class="table table-striped table-hover">
      <thead>
      <tr>
        <th>Pollster</th>
        <th>Last Poll</th>
        <th># Polls</th>
        <th>Avg Days/Poll</th>
        <th>Projected Update</th>
      </tr>
      </thead>
      <tbody>
        @foreach ($contest->rcp_contest_pollsters()->orderBy('name', 'asc')->get() as $pollster)
          @if ($pollster->rcp_contest_polls->count() > 1 && $pollster->isNotOutOfDate())
            <tr>
              <td>{{ $pollster->name }}</td>
              <td>{{ $pollster->latest_poll()->date_end }}: {{ (int) $pollster->latest_poll()->percent_favor }}%</td>
              <td>{{ $pollster->rcp_contest_polls->count() }}</td>
              <td>{{ $pollster->daysPerPoll() }}</td>
              <td>{{ $pollster->projectedUpdate() }}</td>
            </tr>
          @endif
        @endforeach
      </tbody>
    </table>
  @endforeach
@stop

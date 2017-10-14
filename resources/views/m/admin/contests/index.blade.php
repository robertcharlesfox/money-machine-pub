@extends('m.base')

@section('content')

<h1>PredictIt Contests</h1>

<div class="panel panel-default">
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="contests">
                <thead>
                    <tr class="nodrag">
                        <th>Contest Name</th>
                        <th>Category</th>
                        <th>Minutes/Scrape</th>
                        <th>Txt Alert?</th>
                        <th># Related Questions</th>
                        <th width="1%">
                            <a href="/admin/contests/create" class="btn btn-primary btn-xs">Add</a>
                        </th>
                        <th width="1%">
                            <a href="/admin/contests/inactive" class="btn btn-primary btn-xs">See Old</a>
                        </th>
                        <th width="1%">
                            <a href="/admin/contests" class="btn btn-primary btn-xs">See New</a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @if ($pi_contests->count())
                        @foreach ($pi_contests as $pi_contest)
                            <tr id="{{{ $pi_contest->id }}}">
                                <td>{{ $pi_contest->name }}</td>
                                <td>{{ $pi_contest->category }}</td>
                                <td>{{ $pi_contest->rcp_scrape_frequency }} / {{ $pi_contest->pollingreport_scrape_frequency }}</td>
                                <td>{{ $pi_contest->rcp_update_txt_alert ? "Yes" : 'No' }} / {{ $pi_contest->pollingreport_update_txt_alert ? "Yes" : 'No' }}</td>
                                <td><a href="/questions/contest/{{ $pi_contest->id }}">{{ $pi_contest->pi_questions()->count() }} Questions</a></td>
                                <td nowrap="nowrap" align="right">
                                    <a href="/admin/contests/edit/{{ $pi_contest->id }}" class="btn btn-primary btn-xs">Edit</a>
                                </td>
                                <td nowrap="nowrap" align="right">
                                    @if ($pi_contest->active)
                                    <a href="/admin/contests/deactivate/{{ $pi_contest->id }}" class="btn btn-primary btn-xs">Deactivate</a>
                                    @else
                                    <a href="/admin/contests/activate/{{ $pi_contest->id }}" class="btn btn-primary btn-xs">Re-activate</a>
                                    @endif
                                </td>
                                @if ($pi_contest->category == 'competition')
                                <td nowrap="nowrap" align="right">
                                    <a href="/admin/contests/competition/{{ $pi_contest->id }}" class="btn btn-primary btn-xs">Competition</a>
                                </td>
                                @endif
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4">No PredictIt Contests found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

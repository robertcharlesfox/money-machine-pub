@extends('josh/layouts/nobottom')

@section('title')
Drop Analysis
@parent
@stop

@section('content')
<div class="container">
  <h3>RCP Drops {{ count($updates) }} Records</h3>
  <div class="well col-md-12">
  <form id="rcp-drop-query" action="/rcp/projections/drops" method="post">
      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <input type="hidden" name="_method" value="POST">

      <div class="row">
          <div class="col-md-2" id="select-contest">
              <label for="pi_contest_id" class="control-label">Contest:</label>
              <select name="pi_contest_id" id="pi_contest_id" class="form-control">
                  <option value="all">All</option>
                  <option value="1">Obama</option>
                  <option value="3">DoC</option>
                  <option value="8">Congress</option>
                  <option value="187">TvC 2-way</option>
                  <option value="189">TvC 4-way</option>
                  <option value="220">TvC PA</option>
                  <option value="225">TvC FL</option>
                  <option value="221">TvC OH</option>
                  <option value="224">TvC NC</option>
                  <option value="227">TvC NV</option>
                  <option value="191">Clinton Fave</option>
                  <option value="190">Trump Fave</option>
              </select>
          </div>

          <div class="col-md-2" id="select-drop-type">
              <label for="drop_type" class="control-label">Drop Type:</label>
              <select name="drop_type" id="drop_type" class="form-control">
                  <option value="drops_alone">Drops Alone</option>
                  <option value="drops_greater">Drops > Adds</option>
                  <option value="swapouts">Swapouts</option>
                  <option value="all">All Drop Types</option>
                  <option value="adds_greater">Adds > Drops</option>
              </select>
          </div>

          <div class="col-md-2" id="select-day">
              <label for="day_of_week" class="control-label">Weekday:</label>
              <select name="day_of_week" id="day_of_week" class="form-control">
                  <option value="all">All</option>
                  <option value="Monday">Monday</option>
                  <option value="Tuesday">Tuesday</option>
                  <option value="Wednesday">Wednesday</option>
                  <option value="Thursday">Thursday</option>
                  <option value="Friday">Friday</option>
                  <option value="Saturday">Saturday</option>
                  <option value="Sunday">Sunday</option>
              </select>
          </div>

          <div class="col-md-2" id="select-sort">
              <label for="sort_by" class="control-label">Sort By:</label>
              <select name="sort_by" id="sort_by" class="form-control">
                  <option value="date">Date</option>
                  <option value="contest">Contest</option>
              </select>
          </div>

          <div class="col-md-2">
              <button type="submit" id="button-query" class="btn btn-success">Query</button>
          </div>

      </div>
  </form>  
  </div>
</div>

<div class="container">
  @foreach ($updates as $update)
  <div class="panel panel-info">
    <div class="panel-heading" role="tab">
      <div class="row">
        <h4 class="panel-title">
          {{ $update['headline'] }}
        </h4>
        <h5 class="panel-title">
          {{ $update['outcomes'] . ': ' . $update['rcp_averages'] . ' = ' . $update['rcp_change'] }}
        </h5>
      </div>
    </div>
    <div class="panel-body" role="tab">
      <div class="row">
        <div class="col-md-6">
          <table class="table table-bordered">
              <thead>
                  <tr>
                      <th>{{ $update['previous_update']['timestamp'] }}</th>
                      <th align="center">Date</th>
                      <th align="center">Sample</th>
                      <th align="center">Age</th>
                  </tr>
              </thead>
              <tbody>
                  @foreach ($update['previous_update']['pollsters'] as $update_pollster)
                  <tr>
                      <td>{{ $update_pollster['name'] }}</td>
                      <td align="center">{{ $update_pollster['dates'] }}</td>
                      <td align="center">{{ $update_pollster['sample'] }}</td>
                      <td align="center">{{ $update_pollster['age'] }}</td>
                  </tr>
                  @endforeach
              </tbody>
          </table>
        </div>
        <div class="col-md-6">
          <table class="table table-bordered">
              <thead>
                  <tr>
                      <th>{{ $update['this_update']['timestamp'] }}</th>
                      <th align="center">Date</th>
                      <th align="center">Sample</th>
                      <th align="center">Age</th>
                  </tr>
              </thead>
              <tbody>
                  @foreach ($update['this_update']['pollsters'] as $update_pollster)
                  <tr>
                      <td>{{ $update_pollster['name'] }}</td>
                      <td align="center">{{ $update_pollster['dates'] }}</td>
                      <td align="center">{{ $update_pollster['sample'] }}</td>
                      <td align="center">{{ $update_pollster['age'] }}</td>
                  </tr>
                  @endforeach
              </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>
@stop

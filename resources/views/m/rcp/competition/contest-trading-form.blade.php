<form id="contest-{{ $contest->id }}" action="/admin/contests/trading" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="_method" value="POST">
    <input type="hidden" name="contest_id" value="{{ $contest->id }}">
    <div class="row">
        <div class="col-md-6">
          <h3>{{ $contest->name }} Projections</h3>
          <h4>Total of all values: {{ $contest->competition_total }}</h4>  
        </div>

        <div class="col-md-3">
            <label for="max_shares_to_hold" class="control-label">Max Shares to Hold</label>
            <input type="number" name="max_shares_to_hold" value="{{ $contest->max_shares_to_hold }}" class="form-control" />
        </div>

        <div class="col-md-3">
            <label for="shares_per_trade" class="control-label">Shares Per Trade:</label>
            <input type="number" name="shares_per_trade" value="{{ $contest->shares_per_trade }}" class="form-control" />
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <a href="/pi/visit/contest/{{ $contest->id }}" class="btn btn-success">Visit All</a>
            <a href="/pi/trade/contest/{{ $contest->id }}" class="btn btn-danger">Trade All</a>
            <a href="/pi/cancel/contest/{{ $contest->id }}" class="btn btn-default">Cancel All Orders</a>
        </div>

        <div class="col-md-3">
            <label for="auto_trade_this_contest" class="control-label">Auto-Trade?</label>
            @if ($contest->auto_trade_this_contest)
                <input type="checkbox" name="auto_trade_this_contest" id="auto_trade_this_contest" checked="checked" value="1" />
            @else
                <input type="checkbox" name="auto_trade_this_contest" id="auto_trade_this_contest" value="1" />
            @endif
        </div>

        <div class="col-md-3">
            <label for="shares_in_blocking_bid" class="control-label">Shares in Blocking Bid:</label>
            <input type="number" name="shares_in_blocking_bid" value="{{ $contest->shares_in_blocking_bid }}" class="form-control" />
        </div>

        <div class="col-md-3">
            <button type="submit" class="btn btn-warning">Save</button>
        </div>
    </div>
</form>
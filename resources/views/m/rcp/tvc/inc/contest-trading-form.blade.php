<form id="contest-{{ $contest->id }}" action="/admin/contests/trading" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="_method" value="POST">
    <input type="hidden" name="contest_id" value="{{ $contest->id }}">
    <div class="row">
        <div class="col-md-1">
            <span>Implied Bias</span>
        </div>

        <div class="col-md-2">
            <input type="text" name="implied_bias" value="{{ $contest->implied_bias }}" class="form-control" />
        </div>

        <div class="col-md-1">
            <span>Implied Variance</span>
        </div>

        <div class="col-md-2">
            <input type="text" name="implied_variance" value="{{ $contest->implied_variance }}" class="form-control" />
        </div>

        <div class="col-md-1">
            <span>Max $ Risk</span>
        </div>

        <div class="col-md-2">
            <input type="number" name="max_shares_to_hold" value="{{ $contest->max_shares_to_hold }}" class="form-control" />
        </div>

        <div class="col-md-2">
            <label for="auto_trade_this_contest" class="control-label">Auto-Trade?</label>
            @if ($contest->auto_trade_this_contest)
                <input type="checkbox" name="auto_trade_this_contest" id="auto_trade_this_contest" checked="checked" value="1" />
            @else
                <input type="checkbox" name="auto_trade_this_contest" id="auto_trade_this_contest" value="1" />
            @endif
        </div>

{{--
        <div class="col-md-3">
            <label for="shares_per_trade" class="control-label">Shares Per Trade:</label>
            <input type="number" name="shares_per_trade" value="{{ $contest->shares_per_trade }}" class="form-control" />
        </div>

        <div class="col-md-3">
            <label for="shares_in_blocking_bid" class="control-label">Shares in Blocking Bid:</label>
            <input type="number" name="shares_in_blocking_bid" value="{{ $contest->shares_in_blocking_bid }}" class="form-control" />
        </div>
    --}}

        <div class="col-md-1">
            <button type="submit" class="btn btn-warning">Save</button>
        </div>
    </div>
</form>
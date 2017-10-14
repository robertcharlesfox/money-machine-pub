<form class="form-horizontal" id="nazgul-update-{{ $nazgul->id }}" action="/onering/nazgul" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="_method" value="POST">
    <input type="hidden" name="nazgul_id" value="{{ $nazgul->id }}">
    <div class="form-group">
        <div class="col-md-2">
            <label for="buy_or_sell" class="control-label">Action:</label>
            <select name="buy_or_sell" id="buy_or_sell" class="form-control">
                <option value="" />
                @foreach (array('buy', 'sell',) as $buy_or_sell)
                    @if ($nazgul->buy_or_sell == $buy_or_sell)
                        <option selected="selected" value="{{ $buy_or_sell }}">{{ $buy_or_sell }}</option>
                    @else
                        <option value="{{ $buy_or_sell }}">{{ $buy_or_sell }}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label for="yes_or_no" class="control-label">Y/N:</label>
            <select name="yes_or_no" id="yes_or_no" class="form-control">
                <option value="" />
                @foreach (array('Yes', 'No',) as $yes_or_no)
                    @if ($nazgul->yes_or_no == $yes_or_no)
                        <option selected="selected" value="{{ $yes_or_no }}">{{ $yes_or_no }}</option>
                    @else
                        <option value="{{ $yes_or_no }}">{{ $yes_or_no }}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label for="risk" class="control-label">Risk $:</label>
            <input type="number" name="risk" value="{{ $nazgul->risk }}" class="form-control" />
        </div>

        <div class="col-md-2">
            <label for="price_limit" class="control-label">Price Limit:</label>
            <input type="number" name="price_limit" value="{{ $nazgul->price_limit }}" class="form-control" />
        </div>

        <div class="col-md-1">
            @if ($nazgul->auto_trade_me)
                Auto Trade? <input type="checkbox" name="auto_trade_me" id="auto_trade_me" checked="checked" value="1" />
            @else
                Auto Trade? <input type="checkbox" name="auto_trade_me" id="auto_trade_me" value="1" />
            @endif
        </div>

        <div class="col-md-1">
            @if ($nazgul->cancel_first)
                Cancel First? <input type="checkbox" name="cancel_first" id="cancel_first" checked="checked" value="1" />
            @else
                Cancel First? <input type="checkbox" name="cancel_first" id="cancel_first" value="1" />
            @endif
        </div>

        <div class="col-md-1">
            <button type="submit" class="btn btn-success">Save</button>
        </div>
    </div>
</form>

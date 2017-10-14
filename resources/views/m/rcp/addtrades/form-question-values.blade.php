<form id="trade-{{ $trade->id }}" action="/bot/addtrades/values" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="_method" value="POST">
    <input type="hidden" name="trade_id" value="{{ $trade->id }}">
    <div class="col-md-2">
        <label for="shares" class="control-label">Shares:</label>
        <input type="number" name="shares" value="{{ $trade->shares }}" class="form-control" />
    </div>

    <div class="col-md-2">
        <label for="buy_or_sell" class="control-label">Action:</label>
        <select name="buy_or_sell" id="buy_or_sell" class="form-control">
            <option value="" />
            @foreach (array('buy', 'sell',) as $buy_or_sell)
                @if ($trade->buy_or_sell == $buy_or_sell)
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
                @if ($trade->yes_or_no == $yes_or_no)
                    <option selected="selected" value="{{ $yes_or_no }}">{{ $yes_or_no }}</option>
                @else
                    <option value="{{ $yes_or_no }}">{{ $yes_or_no }}</option>
                @endif
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        <label for="price" class="control-label">Price:</label>
        <input type="number" name="price" value="{{ $trade->price }}" class="form-control" />
    </div>

    <div class="col-md-1">
        <label for="auto_trade_me" class="control-label">Auto-Trade?</label>
        @if ($trade->auto_trade_me)
            <input type="checkbox" name="auto_trade_me" id="auto_trade_me" checked="checked" value="1" />
        @else
            <input type="checkbox" name="auto_trade_me" id="auto_trade_me" value="1" />
        @endif
    </div>

    <div class="col-md-1">
        <button type="submit" class="btn btn-success">Save</button>
    </div>

    <div class="col-md-1">
        <a href="/bot/addtrades/deactivate/{{ $trade->id }}" class="btn btn-danger">Cancel</a>
    </div>
</form>
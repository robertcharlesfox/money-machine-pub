@extends('m.base')
@section('content')
<h1>{{ isset($question) ? 'Edit Question' : 'Add Question' }}</h1>

<div class="panel panel-default">
    <div class="panel-body">
        <form action="/admin/questions" method="post">
            <div class="col-md-6">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="POST">
                <input type="hidden" name="question_id" value="{{ isset($question) ? $question->id : '' }}">
                <h2>Information</h2>

                <div class="form-group {{ $errors->has('pi_contest_id') ? 'has-error' : '' }}">
                    <label for="pi_contest_id" class="control-label">Contest:</label>
                    <select name="pi_contest_id" id="pi_contest_id" class="form-control">
                        @if (! isset($question))
                            <option value=""></option>
                        @endif
                        @foreach ($contests as $contest)
                            @if (isset($question) and $question->pi_contest->id === $contest->id)
                                <option selected="selected" value="{{ $contest->id }}">{{ $contest->name }}</option>
                            @else
                                <option value="{{ $contest->id }}">{{ $contest->name }}</option>
                            @endif
                        @endforeach
                    </select>
                    {!! $errors->first('pi_contest_id', '<span class="help-block">:message</span>') !!}
                </div>

                <div class="form-group {{ $errors->has('url_of_market') ? 'has-error' : '' }}">
                    <label for="url_of_market" class="control-label">URL of Market:</label>
                    <input type="text" name="url_of_market" value="{{ old('url_of_market', isset($question) ? $question->url_of_market : null) }}" class="form-control">
                    {!! $errors->first('url_of_market', '<span class="help-block">:message</span>') !!}
                </div>

                <div class="form-group">
                    <label for="auto_trade_me" class="control-label">Auto-Trade Me?</label>
                    @if (isset($question) && $question->auto_trade_me)
                        <input type="checkbox" name="auto_trade_me" id="auto_trade_me" checked="checked" value="1" />
                    @else
                        <input type="checkbox" name="auto_trade_me" id="auto_trade_me" value="1" />
                    @endif
                </div>

                <div class="form-group">
                    <label for="yes_or_no" class="control-label">Buy Yes or No?</label>
                    <select name="yes_or_no" id="yes_or_no" class="form-control">
                        @if (! isset($question))
                            <option value=""></option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        @else
                            @if ($question->yes_or_no =='Yes')
                                <option selected="selected" value="Yes">Yes</option>
                                <option value="No">No</option>
                            @else
                                <option value="Yes">Yes</option>
                                <option selected="selected" value="No">No</option>
                            @endif
                        @endif
                    </select>
                </div>

                <div class="form-group">
                    <label for="buy_price" class="control-label">Buy Price:</label>
                    <input type="number" name="buy_price" value="{{ old('buy_price', isset($question) ? $question->buy_price : null) }}" class="form-control">
                    <label for="buy_shares" class="control-label">Buy Shares:</label>
                    <input type="number" name="buy_shares" value="{{ old('buy_shares', isset($question) ? $question->buy_shares : null) }}" class="form-control">
                </div>

                <div class="form-group">
                    <label for="sell_price" class="control-label">Sell Price:</label>
                    <input type="number" name="sell_price" value="{{ old('sell_price', isset($question) ? $question->sell_price : null) }}" class="form-control">
                    <label for="sell_shares" class="control-label">Sell Shares:</label>
                    <input type="number" name="sell_shares" value="{{ old('sell_shares', isset($question) ? $question->sell_shares : null) }}" class="form-control">
                </div>

                <div class="form-group">
                    <label for="max_shares_owned" class="control-label">Max Shares:</label>
                    <input type="number" name="max_shares_owned" value="{{ old('max_shares_owned', isset($question) ? $question->max_shares_owned : null) }}" class="form-control">
                    <label for="min_shares_owned" class="control-label">Min Shares:</label>
                    <input type="number" name="min_shares_owned" value="{{ old('min_shares_owned', isset($question) ? $question->min_shares_owned : null) }}" class="form-control">
                    <label for="max_open_orders" class="control-label">Max Open Orders:</label>
                    <input type="number" name="max_open_orders" value="{{ old('max_open_orders', isset($question) ? $question->max_open_orders : null) }}" class="form-control">
                </div>

                <div class="form-group">
                    @if (isset($question))
                        <button type="submit" class="btn btn-success">Save Changes</button>
                        <a href="/admin/questions" class="btn btn-default">Done</a>
                    @else
                        <button type="submit" id="button-create" class="btn btn-success">Create</button>
                        <a href="/admin/questions" class="btn btn-default">Cancel</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

@stop

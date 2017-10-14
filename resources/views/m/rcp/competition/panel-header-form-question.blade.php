<form id="question-{{ $question->id }}" action="/admin/questions/competition" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="_method" value="POST">
    <input type="hidden" name="question_id" value="{{ $question->id }}">
    <div class="row">
        <div class="col-md-4">
            <a class="collapsed" role="button" data-toggle="collapse" href="#collapse{{ $question->id }}" aria-expanded="false" aria-controls="collapse{{ $question->id }}">
                {{ $question->question_ticker }}
            </a>
        </div>

        <div class="col-md-2">
            <label for="chance_to_win" class="control-label">Chance to Win:</label>
            <input type="number" name="chance_to_win" value="{{ $question->chance_to_win }}" class="form-control" />
        </div>

        <div class="col-md-2">
            <label for="max_shares_owned" class="control-label">Max Owned:</label>
            <input type="number" name="max_shares_owned" value="{{ $question->max_shares_owned }}" class="form-control" />
        </div>

        <div class="col-md-2">
            <label for="min_shares_owned" class="control-label">Min Owned:</label>
            <input type="number" name="min_shares_owned" value="{{ $question->min_shares_owned }}" class="form-control" />
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-warning">Save</button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <a href="/pi/visit/question/{{ $question->id }}" class="btn btn-success">Visit</a>
            <a href="/pi/trade/question/{{ $question->id }}" class="btn btn-danger">Trade</a>
            <a href="/pi/cancel/question/{{ $question->id }}" class="btn btn-default">Cancel Orders</a>
        </div>

        <div class="col-md-2">
            <label for="auto_trade_me" class="control-label">Auto-Trade?</label>
            @if ($question->auto_trade_me)
                <input type="checkbox" name="auto_trade_me" id="auto_trade_me" checked="checked" value="1" />
            @else
                <input type="checkbox" name="auto_trade_me" id="auto_trade_me" value="1" />
            @endif
        </div>

        <div class="col-md-2">
            <label for="category" class="control-label">Category:</label>
            <select name="category" id="category" class="form-control">
                @foreach ($trade_categories as $category)
                    @if (isset($question) and $question->category == $category)
                        <option selected="selected" value="{{ $category }}">{{ $category }}</option>
                    @else
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label for="fundraising_low" class="control-label">Low End:</label>
            <input type="text" name="fundraising_low" value="{{ $question->fundraising_low }}" class="form-control" />
        </div>

        <div class="col-md-2">
            <label for="fundraising_high" class="control-label">High End:</label>
            <input type="text" name="fundraising_high" value="{{ $question->fundraising_high }}" class="form-control" />
        </div>

        <div class="col-md-2">
            <label for="churn_range" class="control-label">Churn Range:</label>
            <input type="number" name="churn_range" value="{{ $question->churn_range }}" class="form-control" />
        </div>
    </div>
</form>
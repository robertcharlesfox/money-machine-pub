<form id="addtrade" action="/bot/addtrades/definition" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="_method" value="POST">
    {{--  <input type="hidden" name="contest_id" value="{{ $contest->id }}">  --}}

    <div class="row">
        <div class="col-md-3" id="select-contest">
            <label for="pi_contest_id" class="control-label">Contest:</label>
            <select name="pi_contest_id" id="pi_contest_id" class="form-control">
                <option value="" />
                @foreach ($add_trade_contests as $contest)
                    <option value="{{ $contest->id }}" class="option-contest" data-contest-id="{{ $contest->id }}">{{ $contest->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 hidden" id="select-pollster">
            <label for="rcp_contest_pollster_id" class="control-label">Pollster:</label>
            <select name="rcp_contest_pollster_id" id="rcp_contest_pollster_id" class="form-control">
                <option value="" />
                @foreach ($add_trade_pollsters as $pollster)
                    <option value="{{ $pollster['id'] }}" class="option-pollster" data-pollster-contest-id="{{ $pollster['pi_contest_id'] }}">{{ $pollster['name'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2 hidden" id="poll-result">
            <label for="poll_result" class="control-label">Poll Result:</label>
            <input type="number" name="poll_result" class="form-control" />
        </div>

        <div class="col-md-3 hidden" id="select-question">
            <label for="pi_question_id" class="control-label">Question:</label>
            <select name="pi_question_id" id="pi_question_id" class="form-control">
                <option value="" />
                @foreach ($add_trade_questions as $question)
                    <option value="{{ $question['id'] }}" class="option-question" data-question-contest-id="{{ $question['pi_contest_id'] }}">{{ $question['question_ticker'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-1">
            <button type="submit" id="button-save" class="btn btn-success disabled">Save</button>
        </div>
    </div>
</form>
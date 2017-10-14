<form id="droptrade" action="/bot/droptrades/definition" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="_method" value="POST">
    {{--  <input type="hidden" name="contest_id" value="{{ $contest->id }}">  --}}

    <div class="row">
        <div class="col-md-3" id="select-contest">
            <label for="pi_contest_id" class="control-label">Contest:</label>
            <select name="pi_contest_id" id="pi_contest_id" class="form-control">
                <option value="" />
                @foreach ($drop_trade_contests as $contest)
                    <option value="{{ $contest->id }}" class="option-contest" data-contest-id="{{ $contest->id }}">{{ $contest->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 hidden" id="select-pollster-1">
            <label for="rcp_contest_pollster_id_1" class="control-label">Pollster 1:</label>
            <select name="rcp_contest_pollster_id_1" id="rcp_contest_pollster_id_1" class="form-control">
                <option value="" />
                @foreach ($drop_trade_pollsters as $pollster)
                    <option value="{{ $pollster['id'] }}" class="option-pollster" data-pollster-contest-id="{{ $pollster['pi_contest_id'] }}">{{ $pollster['name'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 hidden" id="select-pollster-2">
            <label for="rcp_contest_pollster_id_2" class="control-label">Pollster 2:</label>
            <select name="rcp_contest_pollster_id_2" id="rcp_contest_pollster_id_2" class="form-control">
                <option value="" />
                @foreach ($drop_trade_pollsters as $pollster)
                    <option value="{{ $pollster['id'] }}" class="option-pollster" data-pollster-contest-id="{{ $pollster['pi_contest_id'] }}">{{ $pollster['name'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2 hidden" id="check-autotrade">
            <label for="auto_trade_me" class="control-label">Auto-Trade?</label>
            <input type="checkbox" name="auto_trade_me" id="auto_trade_me" value="1" />
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 hidden" id="select-question">
            <label for="pi_question_id" class="control-label">Question:</label>
            <select name="pi_question_id" id="pi_question_id" class="form-control">
                <option value="" />
                @foreach ($drop_trade_questions as $question)
                    <option value="{{ $question['id'] }}" class="option-question" data-question-contest-id="{{ $question['pi_contest_id'] }}">{{ $question['question_ticker'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 hidden" id="select-pollster-3">
            <label for="rcp_contest_pollster_id_3" class="control-label">Pollster 3:</label>
            <select name="rcp_contest_pollster_id_3" id="rcp_contest_pollster_id_3" class="form-control">
                <option value="" />
                @foreach ($drop_trade_pollsters as $pollster)
                    <option value="{{ $pollster['id'] }}" class="option-pollster" data-pollster-contest-id="{{ $pollster['pi_contest_id'] }}">{{ $pollster['name'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 hidden" id="select-pollster-4">
            <label for="rcp_contest_pollster_id_4" class="control-label">Pollster 4:</label>
            <select name="rcp_contest_pollster_id_4" id="rcp_contest_pollster_id_4" class="form-control">
                <option value="" />
                @foreach ($drop_trade_pollsters as $pollster)
                    <option value="{{ $pollster['id'] }}" class="option-pollster" data-pollster-contest-id="{{ $pollster['pi_contest_id'] }}">{{ $pollster['name'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <button type="submit" id="button-save" class="btn btn-success disabled">Save</button>
        </div>
    </div>
</form>
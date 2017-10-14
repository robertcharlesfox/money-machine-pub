<form id="nazgul-entry" action="/onering/nazgul" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="_method" value="POST">

    <div class="row">
        <div class="col-md-4" id="select-contest">
            <label for="pi_contest_id" class="control-label">Contest:</label>
            <select name="pi_contest_id" id="pi_contest_id" class="form-control">
                <option value="" />
                @foreach ($nazgul_contests as $contest)
                    <option value="{{ $contest->id }}" class="option-contest" data-contest-id="{{ $contest->id }}">{{ $contest->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4 hidden" id="select-question">
            <label for="pi_question_id" class="control-label">Question:</label>
            <select name="pi_question_id" id="pi_question_id" class="form-control">
                <option value="" />
                @foreach ($nazgul_questions as $question)
                    <option value="{{ $question['id'] }}" class="option-question" data-question-contest-id="{{ $question['pi_contest_id'] }}">{{ $question['question_ticker'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <button type="submit" id="button-save" class="btn btn-success disabled">Save</button>
        </div>
    </div>
</form>
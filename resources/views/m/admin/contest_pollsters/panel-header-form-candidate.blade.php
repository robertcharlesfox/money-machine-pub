<form id="contest-pollster-{{ $pollster->id }}" action="/admin/contest_pollsters" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="_method" value="POST">
    <input type="hidden" name="contest_pollster_id" value="{{ $pollster->id }}">

    <div class="form-group">
        <div class="col-md-4">
            <label for="comments" class="control-label">Comments</label>
            <input type="text" name="comments" id="comments" value="{{ old('comments', $pollster->comments) }}" class="form-control"/>
        </div>

        <div class="col-md-2">
            <label for="next_poll_expected" class="control-label">Next Expected</label>
            <input type="date" name="next_poll_expected" id="next_poll_expected" value="{{ old('next_poll_expected', $pollster->next_poll_expected) }}" class="form-control"/>
        </div>

        <div class="col-md-1">
            <label for="is_likely_final_for_week" class="control-label">Likely Final</label>
            @if ($pollster->is_likely_final_for_week)
                <input type="checkbox" name="is_likely_final_for_week" id="is_likely_final_for_week" checked="checked" value="1" />
            @else
                <input type="checkbox" name="is_likely_final_for_week" id="is_likely_final_for_week" value="1" />
            @endif
        </div>

        <div class="col-md-1">
            <label for="is_likely_dropout" class="control-label">Likely Dropout</label>
            @if ($pollster->is_likely_dropout)
                <input type="checkbox" name="is_likely_dropout" id="is_likely_dropout" checked="checked" value="1" />
            @else
                <input type="checkbox" name="is_likely_dropout" id="is_likely_dropout" value="1" />
            @endif
        </div>

        <div class="col-md-1">
            <label for="is_likely_addition" class="control-label">Likely Addition</label>
            @if ($pollster->is_likely_addition)
                <input type="checkbox" name="is_likely_addition" id="is_likely_addition" checked="checked" value="1" />
            @else
                <input type="checkbox" name="is_likely_addition" id="is_likely_addition" value="1" />
            @endif
        </div>

        <div class="col-md-1">
            <label for="debate_eligible_poll" class="control-label">Debate Eligible</label>
            @if ($pollster->debate_eligible_poll)
                <input type="checkbox" name="debate_eligible_poll" id="debate_eligible_poll" checked="checked" value="1" />
            @else
                <input type="checkbox" name="debate_eligible_poll" id="debate_eligible_poll" value="1" />
            @endif
        </div>

        <div class="col-md-1">
            <label for="keep_scraping" class="control-label">Keep Scraping</label>
            @if ($pollster->keep_scraping)
                <input type="checkbox" name="keep_scraping" id="keep_scraping" checked="checked" value="1" />
            @else
                <input type="checkbox" name="keep_scraping" id="keep_scraping" value="1" />
            @endif
        </div>

        <div class="col-md-1">
            <button type="submit" class="btn btn-success">Save</button>
            @if ($pollster->new_poll_update_text)
            <a href="/admin/contest_pollsters/reactivate/{{ $pollster->id }}" class="btn btn-primary btn-xs">Reactivate</a>
            @endif
        </div>
    </div>
</form>
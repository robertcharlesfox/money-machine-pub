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
            <label for="is_likely_addition" class="control-label">Likely Addition</label>
            @if ($pollster->is_likely_addition)
                <input type="checkbox" name="is_likely_addition" id="is_likely_addition" checked="checked" value="1" />
            @else
                <input type="checkbox" name="is_likely_addition" id="is_likely_addition" value="1" />
            @endif
        </div>

        <div class="col-md-1">
            <label for="un_included_actual_result" class="control-label">Actual</label>
            <input type="number" name="un_included_actual_result" value="{{ $pollster->un_included_actual_result }}" class="form-control" />
        </div>

        <div class="col-md-1">
            <label for="projected_result" class="control-label">Projected</label>
            <input type="text" name="projected_result" value="{{ $pollster->projected_result }}" class="form-control" />
        </div>

        <div class="col-md-1">
            <label for="auto_trade_updates" class="control-label">Auto-Trade</label>
            @if ($pollster->auto_trade_updates)
                <input type="checkbox" name="auto_trade_updates" id="auto_trade_updates" checked="checked" value="1" />
            @else
                <input type="checkbox" name="auto_trade_updates" id="auto_trade_updates" value="1" />
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
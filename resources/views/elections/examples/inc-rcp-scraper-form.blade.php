<div class="row">
    <div class="col-md-12">
      <h4>{{ $contest->name }}</h4>
          <form class="form-horizontal" id="rcp-scrapers-{{ $contest->id }}" action="/onering/rcp/scrapers" method="post">
          <input type="hidden" name="_token" value="{{ csrf_token() }}">
          <input type="hidden" name="_method" value="POST">
          <input type="hidden" name="contest_id" value="{{ $contest->id }}">
          <fieldset>
          <!-- Select Basic -->
          <div class="form-group">
            <label class="col-md-2 control-label" for="rcp_scrape_frequency">RCP Scrape Frequency</label>
            <div class="col-md-2">
              <select id="rcp_scrape_frequency" name="rcp_scrape_frequency" class="form-control">
                <option value=""></option>
                @foreach ($scrape_frequencies as $frequency)
                  @if ($contest->rcp_scrape_frequency == $frequency)
                  <option value="{{ $frequency }}" selected="selected">{{ $frequency }}</option>
                  @else
                  <option value="{{ $frequency }}">{{ $frequency }}</option>
                  @endif
                @endforeach
              </select>
            </div>

            <label class="col-md-2 control-label" for="rcp_scrapes_per_minute">RCP Scrapes per Minute</label>
            <div class="col-md-2">
              <select id="rcp_scrapes_per_minute" name="rcp_scrapes_per_minute" class="form-control">
                <option value=""></option>
                @foreach ($scrape_frequencies as $frequency)
                  @if ($contest->rcp_scrapes_per_minute == $frequency)
                  <option value="{{ $frequency }}" selected="selected">{{ $frequency }}</option>
                  @else
                  <option value="{{ $frequency }}">{{ $frequency }}</option>
                  @endif
                @endforeach
              </select>
            </div>

            <div class="col-md-2">
              <button type="submit" class="btn btn-success">Save</button>
            </div>
          </div>
          </fieldset>
          </form>
    </div>
</div>

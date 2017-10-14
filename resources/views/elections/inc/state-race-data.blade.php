    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th></th>
                <th>Dem</th>
                <th>GOP</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>2016</td>
                <td>{{ number_format($race->votes_dem_cached) }}</td>
                <td>{{ number_format($race->votes_gop_cached) }}</td>
                <td>{{ number_format($race->votes_dem_cached + $race->votes_gop_cached + $race->votes_independent_cached + $race->votes_others_cached) }}</td>
            </tr>
            <tr>
                <td>2012</td>
                <td>{{ number_format($state->votes_dem_2012) }}</td>
                <td>{{ number_format($state->votes_gop_2012) }}</td>
                <td>{{ number_format($state->votes_total_2012) }}</td>
            </tr>
            <tr>
                <td>2008</td>
                <td>{{ number_format($state->votes_dem_2008) }}</td>
                <td>{{ number_format($state->votes_gop_2008) }}</td>
                <td>{{ number_format($state->votes_total_2008) }}</td>
            </tr>
            <tr>
                <td>1996</td>
                <td>{{ number_format($state->votes_dem_1996) }}</td>
                <td>{{ number_format($state->votes_gop_1996) }}</td>
                <td>{{ number_format($state->votes_total_1996) }}</td>
            </tr>
        </tbody>
    </table>
</div>
<div class="col-md-8">
    <div class="row"><div class="col-md-12">
        <button data-race-id="{{ $race->id }}" data-dem-chance="100" class="btn btn-xs race-update safe-D">Safe D</button>
        <button data-race-id="{{ $race->id }}" data-dem-chance="85" class="btn btn-xs race-update likely-D">Likely D</button>
        <button data-race-id="{{ $race->id }}" data-dem-chance="65" class="btn btn-xs race-update lean-D">Lean D</button>
        <button data-race-id="{{ $race->id }}" data-dem-chance="50" class="btn btn-xs race-update tossup">Tossup</button>
        <button data-race-id="{{ $race->id }}" data-dem-chance="35" class="btn btn-xs race-update lean-R">Lean R</button>
        <button data-race-id="{{ $race->id }}" data-dem-chance="15" class="btn btn-xs race-update likely-R">Likely R</button>
        <button data-race-id="{{ $race->id }}" data-dem-chance="0" class="btn btn-xs race-update safe-R">Safe R</button>
    </div></div>
    @if ($race->piContest())
    <div class="row"><div class="col-md-12">
        <button onclick="getPriceQuote({{ $race->id }})" class="btn btn-xs btn-success">$</button>
        <button onclick="getVisitMarket({{ $race->id }})" class="btn btn-xs btn-default">Visit</button>
        <div id="price-data-{{ $race->id }}">
        @foreach($race->piContest()->pi_questions as $question)
            {{ $race->piLastPrice($question->question_ticker) }}
            <br>
        @endforeach
        </div>
    </div></div>
    @endif

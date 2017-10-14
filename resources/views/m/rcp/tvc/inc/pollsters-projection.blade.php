@if ($pollster['pollster']->show_as_final)
<tr bgcolor="GreenYellow">
@elseif ($pollster['pollster']->show_as_likely_drop)
<tr bgcolor="Orange">
@elseif ($pollster['pollster']->show_as_possible_drop)
<tr bgcolor="Moccasin">
@elseif ($pollster['pollster']->show_as_likely_update)
<tr bgcolor="Violet">
@else
<tr bgcolor="GhostWhite">
@endif
    <td align="left">{{ $pollster['pollster']->name . ' (' . $pollster['latest_result']['id'] . ')' }}
          <i id="old-polls-show-{{ $pollster['pollster']->id }}" class="glyphicon glyphicon-plus" data-pollster_id="{{ $pollster['pollster']->id }}"></i>
          <i id="old-polls-hide-{{ $pollster['pollster']->id }}" class="glyphicon glyphicon-minus" data-pollster_id="{{ $pollster['pollster']->id }}" style="display:none;"></i>
    </td>
    <td align="center" class="pollster-dates-values">{{ $pollster['latest_result']['dates'] }}</td>

    @foreach ($candidate_names as $name)
      <td align="center" class="analysis-table-results">{{ $pollster['latest_result'][$name] }}</td>
    @endforeach

    @foreach ($candidate_names as $name)
      <td align="center" class="analysis-table-early-results" style="display:none;">
        <input type="text" class="form-control" id="early_{{ $name . '-' . $pollster['pollster']->id }}" name="early_{{ $name }}" value="{{ $pollster['early_result'][$name] }}">
      </td>
    @endforeach

    <td class="analysis-table-selectors" style="display:none;">
      <select id="select-update-{{ $pollster['pollster']->id }}" class="form-control">
          <option value=""></option>
          @foreach ($likelihoods as $name => $value)
            @if ($pollster['pollster']->probability_updated == $value)
            <option value="{{ $value }}" selected="selected">{{ $name }}</option>
            @else
            <option value="{{ $value }}">{{ $name }}</option>
            @endif
          @endforeach
      </select>
    </td>
    <td class="analysis-table-drop-analysis" style="display:none;">
      <select id="select-drop-{{ $pollster['pollster']->id }}" class="form-control">
          <option value=""></option>
          @foreach ($likelihoods as $name => $value)
            @if ($pollster['pollster']->probability_dropped == $value)
            <option value="{{ $value }}" selected="selected">{{ $name }}</option>
            @else
            <option value="{{ $value }}">{{ $name }}</option>
            @endif
          @endforeach
      </select>
    </td>
    <td class="analysis-table-drop-analysis" style="display:none;">
      {{ $pollster['latest_result']['age'] }}
    </td>
    <td align="center" class="analysis-table-release-notes" style="display:none;">
      N/A
    </td>
    <td class="analysis-table-release-notes" style="display:none;">
      <select id="select-frequency-{{ $pollster['pollster']->id }}" class="form-control">
          @foreach (['', 'daily', 'weekly', 'other',] as $value)
            @if ($pollster['pollster']->update_frequency == $value)
            <option value="{{ $value }}" selected="selected">{{ $value }}</option>
            @else
            <option value="{{ $value }}">{{ $value }}</option>
            @endif
          @endforeach
      </select>
    </td>
    <td class="analysis-table-selectors" style="display:none;">
      <select id="select-add-{{ $pollster['pollster']->id }}" class="form-control">
          <option value=""></option>
          @foreach ($likelihoods as $name => $value)
            @if ($pollster['pollster']->probability_added == $value)
            <option value="{{ $value }}" selected="selected">{{ $name }}</option>
            @else
            <option value="{{ $value }}">{{ $name }}</option>
            @endif
          @endforeach
      </select>
    </td>
    <td align="center" class="analysis-table-selectors" style="display:none;">
      <input type="text" class="form-control" id="projected_result-{{ $pollster['pollster']->id }}" name="projected_result" value="{{ $pollster['pollster']->projected_result }}">
    </td>
    <td align="center" id="pollster-value-{{ $pollster['pollster']->id }}" class="pollster-dates-values">{{ $pollster['values_for_average'] }}</td>
    <td align="center">
      <button type="button" class="btn button-xs btn-primary btn-save-pollster"
              data-pollster_id="{{ $pollster['pollster']->id }}">
          <span class="glyphicon glyphicon-save"></span>
      </button>
    </td>
</tr>
@foreach ($pollster['latest_polls'] as $poll)
  @include ('m.rcp.tvc.inc.polls-old')
@endforeach
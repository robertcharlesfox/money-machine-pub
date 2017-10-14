<tr bgcolor="GhostWhite" class="old-polls-{{ $pollster['pollster']->id }}" style="display:none;">
    <td align="left">{{ $poll['added'] }}</td>
    <td align="center" class="pollster-dates-values">{{ $poll['text_date_range'] }}</td>

    @foreach ($candidate_names as $name)
      <td align="center" class="analysis-table-results">{{ $poll[$name] }}</td>
    @endforeach

    <td align="center" class="analysis-table-release-notes" style="display:none;">
      @if ($poll['mark_as_old'])
      <a class="btn button-xs btn-success" href="/admin/polls/old/{{ $poll['id'] }}/0">Not Old</a>
      @else
      <a class="btn button-xs btn-danger" href="/admin/polls/old/{{ $poll['id'] }}/1">Mark Old</a>
      @endif
    </td>
    <td align="center">{{ $poll['dropped'] }}</td>
    <td align="center">{{ $poll['age'] }}</td>
</tr>




@if ($row->balance > 0.009)
    <strong class="text-danger">{{ money($row->balance) }}</strong>
@elseif ($row->balance < -0.009)
    <span class="text-success">{{ money(abs($row->balance)) }} in credit</span>
@else
    <span class="badge badge-soft-success">{{ $labels['settled'] }}</span>
@endif

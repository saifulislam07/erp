{{ money($row->paid) }}
@if ($row->advance > 0.009)
    <small class="d-block text-muted">{{ money($row->advance) }} advance</small>
@endif

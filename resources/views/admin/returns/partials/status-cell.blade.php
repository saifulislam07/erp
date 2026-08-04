<span class="badge badge-{{ ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$return->status] ?? 'secondary' }}">
    {{ ucfirst($return->status) }}
</span>

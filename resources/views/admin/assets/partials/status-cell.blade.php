<span class="badge badge-{{ ['active' => 'success', 'disposed' => 'secondary', 'lost' => 'danger'][$asset->status] ?? 'secondary' }}">
    {{ ucfirst($asset->status) }}
</span>

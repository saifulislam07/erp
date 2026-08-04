<span class="badge badge-{{ $tx->transaction_type === 'credit' ? 'success' : 'danger' }}">
    {{ ucfirst($tx->transaction_type) }}
</span>

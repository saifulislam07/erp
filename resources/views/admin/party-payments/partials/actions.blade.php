@if (auth()->user()->is_admin)
    <form action="{{ route($routePrefix.'.destroy', $payment) }}" method="post" class="d-inline"
          data-confirm="Reverse this payment?"
          data-confirm-text="{{ money($payment->amount) }} will be put back on the invoices it settled and removed from the cash/bank ledger."
          data-confirm-button="Reverse">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" title="Reverse">
            <i class="fas fa-undo"></i>
        </button>
    </form>
@endif

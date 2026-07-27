@extends('layouts.admin')

@section('content_title', 'Client Detail')

@section('content_body')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $client->name }}</h3>
                    <div class="card-tools">
                        <span class="badge {{ $client->status ? 'badge-success' : 'badge-danger' }}">
                            {{ $client->status ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>

                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tr><th style="width: 40%">Unique ID</th><td>{{ $client->unique_id }}</td></tr>
                        <tr>
                            <th>Type</th>
                            <td>
                                <span class="badge badge-{{ $client->type === 'agent' ? 'info' : 'secondary' }}">
                                    {{ ucfirst($client->type) }}
                                </span>
                            </td>
                        </tr>
                        <tr><th>Business</th><td>{{ $client->business_name ?? '-' }}</td></tr>
                        <tr><th>Email</th><td>{{ $client->email ?? '-' }}</td></tr>
                        <tr><th>Phone</th><td>{{ $client->phone ?? '-' }}</td></tr>
                        <tr><th>Address</th><td>{{ $client->address ?? '-' }}</td></tr>
                        <tr><th>Registered</th><td>{{ $client->created_at->format('Y-m-d') }}</td></tr>
                    </table>
                </div>

                <div class="card-footer">
                    <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    @can('message.view')
                        <a href="{{ route('admin.messages.show', $client) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-comments"></i> Messages
                        </a>
                    @endcan
                    <form action="{{ route('admin.clients.reset-password', $client) }}" method="post" class="d-inline reset-form">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm">
                            <i class="fas fa-key"></i> Reset Password
                        </button>
                    </form>
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-default btn-sm">Back</a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            @can('order.view')
                <div class="row">
                    <div class="col-6 col-lg-3">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $orderStats['total'] }}</h3>
                                <p>Total Orders</p>
                            </div>
                            <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ $orderStats['pending'] }}</h3>
                                <p>In Progress</p>
                            </div>
                            <div class="icon"><i class="fas fa-hourglass-half"></i></div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $orderStats['delivered'] }}</h3>
                                <p>Delivered</p>
                            </div>
                            <div class="icon"><i class="fas fa-check"></i></div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="small-box bg-secondary">
                            <div class="inner">
                                <h3>{{ number_format($orderStats['value'], 0) }}</h3>
                                <p>Order Value</p>
                            </div>
                            <div class="icon"><i class="fas fa-coins"></i></div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Orders</h3>
                    </div>
                    <div class="card-body p-0">
                        @if ($recentOrders->isEmpty())
                            <p class="text-muted p-3 mb-0">No orders yet.</p>
                        @else
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th class="text-right">Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentOrders as $order)
                                        <tr>
                                            <td>{{ $order->order_id }}</td>
                                            <td>{{ $order->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                <span class="badge badge-secondary">
                                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                                </span>
                                            </td>
                                            <td class="text-right">{{ number_format($order->total_amount, 2) }}</td>
                                            <td class="text-right">
                                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-xs btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            @endcan

            @can('sale.view')
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Sales</h3>
                    </div>
                    <div class="card-body p-0">
                        @if ($recentSales->isEmpty())
                            <p class="text-muted p-3 mb-0">No sales recorded for this client.</p>
                        @else
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Sale ID</th>
                                        <th>Date</th>
                                        <th>Payment</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-right">Due</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentSales as $sale)
                                        <tr>
                                            <td>{{ $sale->sale_id }}</td>
                                            <td>{{ $sale->sale_date?->format('Y-m-d') ?? $sale->created_at->format('Y-m-d') }}</td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $sale->payment_status)) }}</td>
                                            <td class="text-right">{{ number_format($sale->total_amount, 2) }}</td>
                                            <td class="text-right">{{ number_format($sale->due_amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            @endcan

            @can('return.view')
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Returns</h3>
                    </div>
                    <div class="card-body p-0">
                        @if ($returns->isEmpty())
                            <p class="text-muted p-3 mb-0">No returns from this client.</p>
                        @else
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Return ID</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Requested</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($returns as $return)
                                        <tr>
                                            <td>{{ $return->return_id }}</td>
                                            <td>{{ $return->returnType->name ?? '-' }}</td>
                                            <td>
                                                <span class="badge badge-secondary">{{ ucfirst($return->status) }}</span>
                                            </td>
                                            <td>{{ $return->created_at->format('Y-m-d') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            @endcan
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            $('.reset-form').on('submit', function (e) {
                e.preventDefault();
                const form = this;
                Swal.fire({
                    title: 'Reset password?',
                    text: 'A new random password will be generated and emailed to the client.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, reset it',
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    </script>
@endpush

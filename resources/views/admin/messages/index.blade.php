@extends('layouts.admin')

@section('content_title', 'Messages')

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Conversations</h3>
        </div>

        <div class="card-body p-0">
            @if ($conversations->isEmpty())
                <p class="p-3 text-muted mb-0">No conversations yet.</p>
            @else
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Last Message</th>
                            <th>Unread</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($conversations as $client)
                            <tr>
                                <td>{{ $client->name }} ({{ $client->unique_id }})</td>
                                <td>{{ \Illuminate\Support\Str::limit($client->last_message?->message, 60) }}</td>
                                <td>
                                    @if ($client->unread_count > 0)
                                        <span class="badge badge-danger">{{ $client->unread_count }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.messages.show', $client) }}" class="btn btn-sm btn-primary">
                                        Open
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection

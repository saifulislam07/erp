@extends('layouts.admin')

@section('content_title', 'Messages')

@section('content_body')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Conversations</h3>
                </div>

                <div class="card-body p-0" style="max-height: 550px; overflow-y: auto;">
                    @if ($conversations->isEmpty())
                        <p class="p-3 text-muted mb-0">No conversations yet.</p>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($conversations as $client)
                                <a href="{{ route('admin.messages.index', ['client' => $client->id]) }}"
                                    class="list-group-item list-group-item-action {{ $activeClient && $activeClient->id === $client->id ? 'active' : '' }}">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $client->name }}</strong>
                                        @if ($client->unread_count > 0)
                                            <span class="badge badge-danger">{{ $client->unread_count }}</span>
                                        @endif
                                    </div>
                                    <div class="text-truncate text-sm {{ $activeClient && $activeClient->id === $client->id ? '' : 'text-muted' }}">
                                        {{ \Illuminate\Support\Str::limit($client->last_message?->message, 50) }}
                                    </div>
                                    @if ($client->conversation_resolved)
                                        <span class="badge badge-success mt-1">Resolved</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            @if ($activeClient)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ $activeClient->name }} ({{ $activeClient->unique_id }})</h3>
                        <div class="card-tools">
                            <form action="{{ route('admin.messages.resolve', $activeClient) }}" method="post" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $activeClient->conversation_resolved ? 'btn-secondary' : 'btn-success' }}">
                                    {{ $activeClient->conversation_resolved ? 'Reopen' : 'Mark Resolved' }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card-body" id="message-thread" style="max-height: 400px; overflow-y: auto;">
                        @include('admin.messages.partials.thread', ['messages' => $messages, 'client' => $activeClient])
                    </div>

                    <div class="card-footer">
                        <form id="message-form" action="{{ route('admin.messages.store', $activeClient) }}" method="post" class="d-flex">
                            @csrf
                            <input type="text" name="message" class="form-control mr-2" placeholder="Type a message..." required>
                            <button type="submit" class="btn btn-primary">Send</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-muted text-center">
                        Select a conversation to view messages.
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@if ($activeClient)
    @push('js')
        <script>
            $(function () {
                const thread = document.getElementById('message-thread');
                thread.scrollTop = thread.scrollHeight;

                $('#message-form').on('submit', function (e) {
                    e.preventDefault();
                    const form = this;

                    $.post(form.action, $(form).serialize())
                        .done(() => {
                            form.reset();
                            poll();
                        });
                });

                function poll() {
                    $.get(@json(route('admin.messages.show', $activeClient)), function (data) {
                        let html = '';
                        data.messages.forEach((m) => {
                            const who = m.sender_type === 'admin' ? 'You' : '{{ $activeClient->name }}';
                            const align = m.sender_type === 'admin' ? 'text-right' : 'text-left';
                            html += `<div class="mb-2 ${align}"><strong>${who}:</strong> ${$('<div>').text(m.message).html()}<br><small class="text-muted">${m.created_at}</small></div>`;
                        });
                        thread.innerHTML = html;
                        thread.scrollTop = thread.scrollHeight;
                    }, 'json');
                }

                setInterval(poll, 10000);
            });
        </script>
    @endpush
@endif

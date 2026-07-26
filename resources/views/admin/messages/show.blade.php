@extends('layouts.admin')

@section('content_title', 'Conversation with ' . $client->name)

@section('content_body')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $client->name }} ({{ $client->unique_id }})</h3>
            <div class="card-tools">
                <a href="{{ route('admin.messages.index') }}" class="btn btn-secondary btn-sm">Back</a>
            </div>
        </div>

        <div class="card-body" id="message-thread" style="max-height: 400px; overflow-y: auto;">
            @include('admin.messages.partials.thread', ['messages' => $messages])
        </div>

        <div class="card-footer">
            <form id="message-form" action="{{ route('admin.messages.store', $client) }}" method="post" class="d-flex">
                @csrf
                <input type="text" name="message" class="form-control mr-2" placeholder="Type a message..." required>
                <button type="submit" class="btn btn-primary">Send</button>
            </form>
        </div>
    </div>
@endsection

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
                $.get(@json(route('admin.messages.show', $client)), function (data) {
                    let html = '';
                    data.messages.forEach((m) => {
                        const who = m.sender_type === 'admin' ? 'You' : '{{ $client->name }}';
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

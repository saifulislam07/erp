@extends('layouts.client')

@section('title', 'Messages')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Conversation with Support</h3>
        </div>

        <div class="card-body" id="message-thread" style="max-height: 400px; overflow-y: auto;">
            @foreach ($messages as $message)
                <div class="mb-2 {{ $message->sender_type === 'client' ? 'text-right' : 'text-left' }}">
                    <strong>{{ $message->sender_type === 'client' ? 'You' : 'Support' }}:</strong>
                    {{ $message->message }}
                    <br>
                    <small class="text-muted">{{ $message->created_at }}</small>
                </div>
            @endforeach
        </div>

        <div class="card-footer">
            <form id="message-form" action="{{ route('client.messages.store') }}" method="post" class="d-flex">
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
                $.get(@json(route('client.messages.index')), function (data) {
                    let html = '';
                    data.messages.forEach((m) => {
                        const who = m.sender_type === 'client' ? 'You' : 'Support';
                        const align = m.sender_type === 'client' ? 'text-right' : 'text-left';
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

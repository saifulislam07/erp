@foreach ($messages as $message)
    <div class="mb-2 {{ $message->sender_type === 'admin' ? 'text-right' : 'text-left' }}">
        <strong>{{ $message->sender_type === 'admin' ? 'You' : $client->name }}:</strong>
        {{ $message->message }}
        <br>
        <small class="text-muted">{{ $message->created_at }}</small>
    </div>
@endforeach

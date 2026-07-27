@component('mail::message')
# Mail is working

This is a test message from **{{ $company }}**, requested by {{ $requestedBy }}.

If you are reading it, the mail server details saved in Settings are correct and
the system can send invoices, password resets and order notifications.

@component('mail::panel')
Sent {{ now()->format('d M Y \a\t H:i') }}
@endcomponent

Nothing else is needed — you can close this message.

Thanks,<br>
{{ $company }}
@endcomponent

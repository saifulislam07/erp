@component('mail::message')
# {{ $isSale ? 'Your invoice' : 'Purchase order' }} {{ $reference }}

Hello {{ $partyName }},

@if ($isSale)
Thank you for your business. Your invoice dated {{ $dated?->format('d M Y') }} is attached to this email.
@else
Please find our purchase order dated {{ $dated?->format('d M Y') }} attached to this email.
@endif

@component('mail::table')
|                 |                          |
|:----------------|-------------------------:|
| Reference       | {{ $reference }}         |
| Total           | {{ money($total) }}      |
@if ($due > 0)
| Balance due     | **{{ money($due) }}**    |
@else
| Status          | Settled in full          |
@endif
@endcomponent

@if ($note)
{{ $note }}
@endif

@if ($isSale && $due > 0)
Please quote the reference above when making payment.
@endif

Thanks,<br>
{{ $company }}
@endcomponent

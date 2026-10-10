Hi Admin,

You’ve just received a new chauffeur service booking request from {{ $lead->payload['name'] }}. Please review the booking details below and get in touch with the client to confirm the reservation.

Booking Details
@foreach ($rows as $label => $value)
{{ $label }}: {{ $value }}
@endforeach
@if ($lead->source_path)

Submitted from: {{ $lead->source_page_title ?: $lead->source_path }} ({{ $lead->source_path }})
@endif

Please contact the customer as soon as possible to confirm the booking and provide any further assistance.

Thanks,
The Chauffeured Solutions Booking System
chauffeuredsolutions.com.au

<p>Hi Admin,</p>
<p>You’ve just received a new chauffeur service booking request from {{ $lead->payload['name'] }}. Please review the booking details below and get in touch with the client to confirm the reservation.</p>
<h3>Booking Details</h3>
<table cellpadding="6" style="border-collapse:collapse">
    @foreach ($rows as $label => $value)
        <tr><td><strong>{{ $label }}:</strong></td><td>{{ $value }}</td></tr>
    @endforeach
</table>
@if ($lead->source_path)
    <p style="color:#666;font-size:13px">Submitted from: {{ $lead->source_page_title ?: $lead->source_path }} ({{ $lead->source_path }})</p>
@endif
<p>Please contact the customer as soon as possible to confirm the booking and provide any further assistance.</p>
<p>Thanks,<br>The Chauffeured Solutions Booking System<br>chauffeuredsolutions.com.au</p>

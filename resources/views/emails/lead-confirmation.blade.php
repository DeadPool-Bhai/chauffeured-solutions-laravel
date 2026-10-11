<p>Hi {{ $lead->contact_name }},</p>
<p>Thank you for choosing Chauffeured Solutions. We’ve successfully received your chauffeur service request. Our team is now reviewing your booking and will be in touch shortly to confirm all the details.</p>
<h3>Your Submitted Details</h3>
<table cellpadding="6" style="border-collapse:collapse">
    @foreach ($rows as $label => $value)
        <tr><td><strong>{{ $label }}:</strong></td><td>{{ $value }}</td></tr>
    @endforeach
</table>
<p>If you'd prefer to speak with one of our expert chauffeurs directly, feel free to contact Paul at {{ config('site.mobile') }}, or use the live chat feature on our website at chauffeuredsolutions.com.au.</p>
<p>We truly appreciate your interest in Chauffeured Solutions and look forward to providing you with a seamless, professional travel experience.</p>
<p>Warm regards,<br>The Chauffeured Solutions Team<br>chauffeuredsolutions.com.au</p>

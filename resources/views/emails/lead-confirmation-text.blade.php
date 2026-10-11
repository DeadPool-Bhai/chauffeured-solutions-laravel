Hi {{ $lead->contact_name }},

Thank you for choosing Chauffeured Solutions. We’ve successfully received your chauffeur service request. Our team is now reviewing your booking and will be in touch shortly to confirm all the details.

Your Submitted Details
@foreach ($rows as $label => $value)
{{ $label }}: {{ $value }}
@endforeach

If you'd prefer to speak with one of our expert chauffeurs directly, feel free to contact Paul at {{ config('site.mobile') }}, or use the live chat feature on our website at chauffeuredsolutions.com.au.

We truly appreciate your interest in Chauffeured Solutions and look forward to providing you with a seamless, professional travel experience.

Warm regards,
The Chauffeured Solutions Team
chauffeuredsolutions.com.au

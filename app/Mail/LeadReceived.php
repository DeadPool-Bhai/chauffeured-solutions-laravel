<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class LeadReceived extends Mailable
{
    public function __construct(public Lead $lead) {}

    public function envelope(): Envelope
    {
        $p = $this->lead->payload;

        return new Envelope(
            subject: 'New Chauffeur Service Booking request from '.$p['name'],
            replyTo: [new Address($p['email'], $p['name'])],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.lead-received', text: 'emails.lead-received-text', with: ['lead' => $this->lead, 'rows' => LeadRows::for($this->lead)]);
    }
}

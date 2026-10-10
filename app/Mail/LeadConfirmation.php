<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class LeadConfirmation extends Mailable
{
    public function __construct(public Lead $lead) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Thank you '.$this->lead->payload['name'].', We’re Processing Your Chauffeur Booking Request');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.lead-confirmation', text: 'emails.lead-confirmation-text', with: ['lead' => $this->lead, 'rows' => LeadRows::for($this->lead)]);
    }
}

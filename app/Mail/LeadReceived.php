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
        $subject = $this->lead->formConfig()['subject'] ?? 'New Chauffeur Service Booking request from :name';
        $email = $this->lead->contact_email;

        return new Envelope(
            subject: str_replace(':name', $this->lead->contact_name, $subject),
            replyTo: $email ? [new Address($email, $this->lead->contact_name)] : [],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.lead-received', text: 'emails.lead-received-text', with: ['lead' => $this->lead, 'rows' => LeadRows::for($this->lead)]);
    }
}

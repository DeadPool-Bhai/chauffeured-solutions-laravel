<?php

namespace App\Jobs;

use App\Mail\LeadConfirmation;
use App\Mail\LeadReceived;
use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendLeadEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public function __construct(public int $leadId) {}

    public function handle(): void
    {
        $lead = Lead::find($this->leadId);
        if (! $lead || $lead->notified_at) {
            return;
        }

        $to = config('leads.notify_to');
        if ($to) {
            Mail::to($to)->send(new LeadReceived($lead));
        }
        if ($lead->contact_email) {
            Mail::to($lead->contact_email)->send(new LeadConfirmation($lead));
        }

        $lead->forceFill(['notified_at' => now()])->save();
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuoteRequest;
use App\Jobs\SendLeadEmails;
use App\Models\Lead;
use App\Services\Recaptcha;

class LeadController extends Controller
{
    public function quote(QuoteRequest $request, Recaptcha $recaptcha)
    {
        if (! $recaptcha->passes($request->input('g-recaptcha-response'), $request->ip())) {
            return back()->withInput()->withErrors(['captcha' => 'We could not verify your request. Please try again, or call us on '.config('site.phone').'.']);
        }

        $data = $request->safe()->except(['g-recaptcha-response', 'website', 'page_title', 'source_path']);

        $lead = Lead::create([
            'form_key' => 'quote',
            'payload' => $data,
            'source_page_title' => $request->input('page_title'),
            'source_path' => $request->input('source_path'),
            'referer' => substr((string) $request->headers->get('referer'), 0, 500) ?: null,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        SendLeadEmails::dispatch($lead->id);

        // away() keeps the trailing slash, which url() would trim. The live thank-you URL has one.
        return redirect()->away($request->getSchemeAndHttpHost().config('leads.thank_you_path'));
    }
}

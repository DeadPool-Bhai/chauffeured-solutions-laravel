<?php

namespace Tests\Feature;

use App\Jobs\SendLeadEmails;
use App\Mail\LeadConfirmation;
use App\Mail\LeadReceived;
use App\Models\Lead;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class QuoteFormTest extends TestCase
{
    use RefreshDatabase;

    private function valid(array $over = []): array
    {
        return array_merge([
            'name' => 'Jane Citizen',
            'email' => 'jane@example.com',
            'phone' => '0412 345 678',
            'pickup_at' => '2026-12-01T09:30',
            'flight_info' => 'QF401',
            'pickup_address' => '1 George St',
            'pickup_city' => 'Sydney',
            'pickup_state' => 'NSW',
            'pickup_zip' => '2000',
            'dropoff_address' => 'Sydney Airport T1',
            'passengers' => 3,
            'luggage' => 2,
            'page_title' => 'Cherrybrook Limo Hire - Chauffeured Solutions',
            'source_path' => '/service-area/cherrybrook-limo-hire/',
        ], $over);
    }

    public function test_valid_submission_saves_a_lead_queues_emails_and_redirects_to_thank_you(): void
    {
        Bus::fake();

        $this->post('/lead/quote', $this->valid())->assertRedirect('http://localhost/thank-you-f4-quote-request/');

        $lead = Lead::firstOrFail();
        $this->assertSame('quote', $lead->form_key);
        $this->assertSame('Jane Citizen', $lead->payload['name']);
        $this->assertSame(3, (int) $lead->payload['passengers']);
        $this->assertSame('/service-area/cherrybrook-limo-hire/', $lead->source_path);
        $this->assertArrayNotHasKey('website', $lead->payload);
        Bus::assertDispatched(SendLeadEmails::class);
    }

    public function test_job_sends_admin_and_customer_emails_once(): void
    {
        Mail::fake();
        config(['leads.notify_to' => ['paul@example.com', 'booking@example.com']]);
        $lead = Lead::create(['form_key' => 'quote', 'payload' => $this->valid()]);

        (new SendLeadEmails($lead->id))->handle();
        (new SendLeadEmails($lead->id))->handle(); // idempotent

        Mail::assertSent(LeadReceived::class, 1);
        Mail::assertSent(LeadReceived::class, fn ($m) => $m->hasTo('paul@example.com') && $m->hasTo('booking@example.com') && $m->hasReplyTo('jane@example.com'));
        Mail::assertSent(LeadConfirmation::class, fn ($m) => $m->hasTo('jane@example.com'));
        $this->assertNotNull($lead->fresh()->notified_at);
    }

    public function test_admin_email_lists_passengers_and_luggage(): void
    {
        $lead = Lead::create(['form_key' => 'quote', 'payload' => $this->valid()]);

        $html = (new LeadReceived($lead))->render();

        $this->assertStringContainsString('Number of Passengers', $html);
        $this->assertStringContainsString('Luggage Count', $html);
        $this->assertStringContainsString('Sydney Airport T1', $html);
        $this->assertStringContainsString('Tue 1 Dec 2026, 9:30 am', $html);
    }

    public function test_validation_failures_do_not_create_a_lead(): void
    {
        $this->from('/x/')->post('/lead/quote', $this->valid(['email' => 'nope', 'passengers' => 0, 'phone' => 'abc']))
            ->assertRedirect('/x/')
            ->assertSessionHasErrors(['email', 'passengers', 'phone']);

        $this->assertSame(0, Lead::count());
    }

    public function test_honeypot_rejects_bots(): void
    {
        $this->post('/lead/quote', $this->valid(['website' => 'http://spam.test']))->assertSessionHasErrors('website');

        $this->assertSame(0, Lead::count());
    }

    public function test_recaptcha_low_score_is_rejected_and_good_score_passes(): void
    {
        Bus::fake();
        config(['leads.recaptcha.secret' => 'secret']);

        Http::fake(['www.google.com/*' => Http::sequence()
            ->push(['success' => true, 'score' => 0.1, 'action' => 'quote'])
            ->push(['success' => true, 'score' => 0.9, 'action' => 'quote'])]);
        $this->post('/lead/quote', $this->valid(['g-recaptcha-response' => 't']))->assertSessionHasErrors('captcha');
        $this->assertSame(0, Lead::count());

        $this->post('/lead/quote', $this->valid(['g-recaptcha-response' => 't']))->assertRedirect('http://localhost/thank-you-f4-quote-request/');
        $this->assertSame(1, Lead::count());
    }

    public function test_missing_recaptcha_token_is_rejected_when_enabled(): void
    {
        config(['leads.recaptcha.secret' => 'secret']);

        $this->post('/lead/quote', $this->valid())->assertSessionHasErrors('captcha');
    }

    public function test_form_renders_on_suburb_page_and_in_the_modal(): void
    {
        Page::factory()->create([
            'canonical_path' => '/service-area/x/',
            'type' => 'suburb',
            'body_html' => '<h2>Get a Customized Quote</h2>[fluentform id="4"]',
        ]);

        $this->getRaw('/service-area/x/')
            ->assertOk()
            ->assertSee('action="'.url('/lead/quote').'"', false)
            ->assertSee('name="pickup_at"', false)
            ->assertSee('name="luggage"', false)
            ->assertSee('value="modal"', false);
    }

    public function test_throttle_limits_bursts(): void
    {
        Bus::fake();
        for ($i = 0; $i < 6; $i++) {
            $this->post('/lead/quote', $this->valid());
        }

        $this->post('/lead/quote', $this->valid())->assertStatus(429);
    }
}

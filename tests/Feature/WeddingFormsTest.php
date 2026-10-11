<?php

namespace Tests\Feature;

use App\Jobs\SendLeadEmails;
use App\Mail\LeadConfirmation;
use App\Mail\LeadReceived;
use App\Models\Lead;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WeddingFormsTest extends TestCase
{
    use RefreshDatabase;

    public function test_shortcode_ids_render_the_right_form(): void
    {
        Page::factory()->create(['canonical_path' => '/wedding-chauffeur/', 'body_html' => '<p>x</p>[fluentform id="9"]']);
        Page::factory()->create(['canonical_path' => '/wedding-chauffeur-booking-form/', 'body_html' => '[fluentform id="7"]']);
        $this->getRaw('/wedding-chauffeur/')->assertSee('Wedding Date')->assertSee('Number of cars required');
        $this->getRaw('/wedding-chauffeur-booking-form/')->assertSee('Designated Contact')->assertSee('Gateway Car');
    }

    public function test_wedding_quote_saves_and_redirects_to_thank_you(): void
    {
        Bus::fake();
        $this->post('/lead/form/wedding-quote', ['name' => 'Amy', 'email' => 'amy@example.com', 'phone' => '0400 111 222', 'wedding_date' => '2027-03-20', 'cars' => 2])
            ->assertRedirect('http://localhost'.config('leads.thank_you_path'));
        $lead = Lead::first();
        $this->assertSame('wedding_quote', $lead->form_key);
        $this->assertSame('Amy', $lead->contact_name);
        Bus::assertDispatched(SendLeadEmails::class);
    }

    public function test_wedding_quote_requires_email_phone_and_date(): void
    {
        $this->post('/lead/form/wedding-quote', ['name' => 'Amy'])->assertSessionHasErrors(['email', 'phone', 'wedding_date']);
        $this->assertSame(0, Lead::count());
    }

    public function test_booking_form_needs_terms_and_stays_on_page_with_message(): void
    {
        $this->post('/lead/form/wedding-booking', ['brides_name' => 'Bea'])->assertSessionHasErrors(['terms']);
        $this->from('/wedding-chauffeur-booking-form/')->post('/lead/form/wedding-booking', ['brides_name' => 'Bea', 'brides_phone' => '0400 000 111', 'ceremony_time' => '14:30', 'terms' => '1'])
            ->assertRedirect('/wedding-chauffeur-booking-form/')->assertSessionHas('form_sent_wedding-booking');
        $lead = Lead::first();
        $this->assertSame('wedding_booking', $lead->form_key);
        $this->assertArrayNotHasKey('terms', $lead->payload);
    }

    public function test_honeypot_blocks_bots(): void
    {
        $this->post('/lead/form/wedding-quote', ['email' => 'a@b.com', 'phone' => '0400 111 222', 'wedding_date' => '2027-03-20', 'website' => 'spam'])->assertSessionHasErrors('website');
    }

    public function test_emails_for_form_without_email_skip_the_confirmation(): void
    {
        Mail::fake();
        $lead = Lead::create(['form_key' => 'wedding_booking', 'payload' => ['brides_name' => 'Bea', 'ceremony_time' => '14:30', 'wedding_date' => '2027-03-20']]);
        (new SendLeadEmails($lead->id))->handle();
        Mail::assertSent(LeadReceived::class, fn ($m) => $m->envelope()->subject === 'New Wedding Car Hire booking form from Bea');
        Mail::assertNotSent(LeadConfirmation::class);
    }
}

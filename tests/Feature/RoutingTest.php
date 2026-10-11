<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Redirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_slash_page_returns_200_and_twin_redirects_with_query(): void
    {
        Page::factory()->create(['canonical_path' => '/about-us/']);

        $this->getRaw('/about-us/')->assertOk();
        $this->getRaw('/about-us?utm_source=x')->assertStatus(301)->assertRedirect('/about-us/?utm_source=x');
    }

    public function test_no_slash_page_returns_200_and_slash_twin_redirects(): void
    {
        Page::factory()->create(['canonical_path' => '/hotel-transfers']);

        $this->get('/hotel-transfers')->assertOk();
        $this->getRaw('/hotel-transfers/')->assertStatus(301)->assertRedirect('/hotel-transfers');
    }

    public function test_redirect_rule_beats_a_page(): void
    {
        Page::factory()->create(['canonical_path' => '/corporate-sedan/']);
        Redirect::factory()->create(['from_path' => '/corporate-sedan/', 'to_path' => '/corporate-chauffeur/']);

        $this->getRaw('/corporate-sedan/')->assertStatus(301)->assertRedirect('/corporate-chauffeur/');
        $this->get('/corporate-sedan')->assertStatus(301)->assertRedirect('/corporate-chauffeur/');
    }

    public function test_absolute_target_and_hit_counter(): void
    {
        $rule = Redirect::factory()->create(['from_path' => '/old', 'to_path' => 'https://example.com/new']);

        $this->get('/old')->assertStatus(301)->assertRedirect('https://example.com/new');
        $this->assertSame(1, $rule->fresh()->hits);
        $this->assertNotNull($rule->fresh()->last_hit_at);
    }

    public function test_gone_rule_returns_410(): void
    {
        Redirect::factory()->create(['from_path' => '/favicon.ico', 'to_path' => null, 'status_code' => 410]);

        $this->get('/favicon.ico')->assertStatus(410);
    }

    public function test_inactive_rule_is_ignored(): void
    {
        Redirect::factory()->create(['from_path' => '/off', 'is_active' => false]);

        $this->get('/off')->assertNotFound();
    }

    public function test_draft_and_unknown_paths_404(): void
    {
        Page::factory()->draft()->create(['canonical_path' => '/security-escorted-service/']);

        $this->getRaw('/security-escorted-service/')->assertNotFound();
        $this->getRaw('/nothing-here/')->assertNotFound();
    }

    public function test_head_tags_are_rendered(): void
    {
        config(['app.url' => 'https://chauffeuredsolutions.com.au']);
        Page::factory()->create([
            'canonical_path' => '/thank-you/',
            'seo_title' => 'Thanks',
            'meta_description' => 'Desc here',
            'robots' => 'noindex, follow',
        ]);

        $this->getRaw('/thank-you/')
            ->assertOk()
            ->assertSee('<title>Thanks</title>', false)
            ->assertSee('<meta name="description" content="Desc here">', false)
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee('<link rel="canonical" href="https://chauffeuredsolutions.com.au/thank-you/">', false);
    }

    public function test_post_requests_are_not_routed(): void
    {
        Page::factory()->create(['canonical_path' => '/about-us/']);

        $this->post('/about-us/')->assertStatus(405);
    }
}

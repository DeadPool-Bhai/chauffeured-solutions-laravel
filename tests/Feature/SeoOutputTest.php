<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Redirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoOutputTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemaps_list_only_indexable_published_urls_in_canonical_form(): void
    {
        config(['app.url' => 'https://chauffeuredsolutions.com.au']);
        Page::factory()->create(['canonical_path' => '/about-us/']);
        Page::factory()->create(['canonical_path' => '/hotel-transfers']);
        Page::factory()->create(['canonical_path' => '/thank-you/', 'robots' => 'noindex, follow']);
        Page::factory()->draft()->create(['canonical_path' => '/draft/']);
        Page::factory()->create(['canonical_path' => '/corporate-sedan/']);
        Redirect::factory()->create(['from_path' => '/corporate-sedan/', 'to_path' => '/corporate-hire/']);
        Page::factory()->create(['canonical_path' => '/privacy-policy/']);
        Page::factory()->create(['kind' => 'post', 'type' => 'post', 'canonical_path' => '/blog/a-post']);

        $pages = $this->getRaw('/page-sitemap.xml');
        $pages->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $xml = $pages->getContent();

        $this->assertStringStartsWith('<?xml', $xml);
        $this->assertStringContainsString('<loc>https://chauffeuredsolutions.com.au/about-us/</loc>', $xml);
        $this->assertStringContainsString('<loc>https://chauffeuredsolutions.com.au/hotel-transfers</loc>', $xml);
        foreach (['/thank-you/', '/draft/', '/corporate-sedan/', '/privacy-policy/', '/blog/a-post'] as $absent) {
            $this->assertStringNotContainsString($absent, $xml);
        }

        $this->getRaw('/post-sitemap.xml')->assertSee('https://chauffeuredsolutions.com.au/blog/a-post', false);
        $this->getRaw('/sitemap_index.xml')->assertOk()
            ->assertSee('https://chauffeuredsolutions.com.au/page-sitemap.xml', false)
            ->assertSee('https://chauffeuredsolutions.com.au/post-sitemap.xml', false);
    }

    public function test_robots_txt_blocks_admin_and_points_at_the_sitemap(): void
    {
        config(['app.url' => 'https://chauffeuredsolutions.com.au']);

        $body = $this->getRaw('/robots.txt')->assertOk()->getContent();

        $this->assertStringContainsString("User-agent: *\n", $body);
        $this->assertStringContainsString('Disallow: /admin/', $body);
        $this->assertStringContainsString('Sitemap: https://chauffeuredsolutions.com.au/sitemap_index.xml', $body);
        $this->assertStringNotContainsString('Disallow: /'."\n", $body);
    }

    public function test_schema_json_is_rendered_verbatim_in_head(): void
    {
        $ld = '<script type="application/ld+json">{"@context":"https://schema.org","@type":"Service","name":"Airport"}</script>';
        Page::factory()->create(['canonical_path' => '/airport/', 'schema_json' => $ld]);
        Page::factory()->create(['canonical_path' => '/plain/']);

        $this->getRaw('/airport/')->assertSee($ld, false);
        $this->getRaw('/plain/')->assertDontSee('application/ld+json', false);
    }

    public function test_open_graph_and_twitter_tags_use_the_seo_fields(): void
    {
        config(['app.url' => 'https://chauffeuredsolutions.com.au']);
        Page::factory()->create(['canonical_path' => '/vip/', 'seo_title' => 'VIP Title', 'meta_description' => 'VIP desc', 'featured_image' => '/wp-content/uploads/2025/vip.jpg']);

        $this->getRaw('/vip/')
            ->assertSee('<meta property="og:title" content="VIP Title">', false)
            ->assertSee('<meta property="og:description" content="VIP desc">', false)
            ->assertSee('<meta property="og:url" content="https://chauffeuredsolutions.com.au/vip/">', false)
            ->assertSee('<meta property="og:image" content="https://chauffeuredsolutions.com.au/wp-content/uploads/2025/vip.jpg">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
    }

    public function test_booking_confirmed_page_is_noindex_and_not_in_the_sitemap(): void
    {
        $this->seed(\Database\Seeders\BookingConfirmedSeeder::class);
        $this->getRaw('/booking-confirmed/')->assertOk()->assertSee('noindex', false)->assertSee('Your booking is confirmed');
        $this->get('/page-sitemap.xml')->assertDontSee('booking-confirmed');
    }
}

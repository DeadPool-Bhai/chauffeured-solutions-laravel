<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_template_renders_data_sections_and_prose(): void
    {
        Page::factory()->create([
            'canonical_path' => '/',
            'type' => 'home',
            'h1' => 'Home H1',
            'body_html' => '<h2>Superior Quality Transfers</h2><p>Intro copy.</p><h2>Frequently Asked Questions</h2><h3>How far ahead?</h3><p>24 hours.</p>',
            'data' => [
                'hero' => ['sub' => 'Hero sub', 'video' => '/v.mp4'],
                'services' => ['eyebrow' => 'About', 'title' => 'Services title', 'intro' => 'x', 'cards' => [['title' => 'VIP', 'img' => '/a.jpg', 'href' => '/vip/']]],
                'fleet' => ['eyebrow' => 'Ride', 'title' => 'Fleet title', 'intro' => 'x', 'cards' => [['title' => 'Audi', 'cat' => 'SUV', 'img' => '/b.jpg', 'href' => '/booking/']]],
                'stats' => ['24/7 Customer Support'],
            ],
        ]);

        $this->getRaw('/')
            ->assertOk()
            ->assertSee('<h1', false)
            ->assertSeeText('Home H1')
            ->assertSeeText('Services title')
            ->assertSeeText('Superior Quality Transfers')
            ->assertSeeText('How far ahead?')
            ->assertSee('href="/vip/"', false)
            ->assertSee('https://book.mylimobiz.com/v4/chausol', false);
    }

    public function test_suburb_template_places_quote_reviews_faq_and_cta(): void
    {
        Page::factory()->create([
            'canonical_path' => '/service-area/x-limo-hire/',
            'type' => 'suburb',
            'h1' => 'X Limo Hire',
            'body_html' => '<p><strong>X Limousine Hire</strong> offers luxury.</p><p><a href="https://book.mylimobiz.com/v4/chausol">Quick Booking</a></p>'
                .'<h2>Get a Customized Quote</h2>[fluentform id="4"]<h2>Services We Offer in X</h2><p>Body.</p>'
                .'<h2>Ready to Experience X?</h2><h2>Testimonials &amp; Client Success Stories</h2>[trustindex no-registration=google]'
                .'<h2>FAQs</h2><h3>Do you cover X?</h3><p>Yes.</p>',
        ]);

        $this->getRaw('/service-area/x-limo-hire/')
            ->assertOk()
            ->assertSeeText('X Limo Hire')
            ->assertSeeText('Services We Offer in X')
            ->assertSeeText('Google reviews')
            ->assertSeeText('Do you cover X?')
            ->assertSeeText('Ready to Experience X?')
            ->assertDontSee('[fluentform', false)
            ->assertDontSee('[trustindex', false);
    }

    public function test_default_template_expands_shortcodes_and_hides_internal_markers(): void
    {
        Page::factory()->create(['canonical_path' => '/contact-us/', 'h1' => 'Contact', 'body_html' => '<p>Hi</p>[fluentform id="4"][8226]']);

        $this->getRaw('/contact-us/')->assertOk()->assertDontSee('[fluentform', false)->assertDontSee('[8226]', false);
    }

    public function test_service_pages_use_the_quote_layout_and_collect_related_posts(): void
    {
        \App\Models\Page::factory()->create([
            'canonical_path' => '/people-mover/', 'h1' => 'People Mover',
            'body_html' => '<p>Intro</p><h2>Get a Free Quote Today!</h2>[fluentform id="4"]<h2>Related posts</h2><h2><a href="/blog/x/"> Post X </a></h2>',
        ]);
        $this->getRaw('/people-mover/')->assertOk()->assertSee('Related posts')->assertSee('Post X')->assertSee('name="website"', false);
    }

    public function test_post_template_shows_date_and_cta(): void
    {
        \App\Models\Page::factory()->create([
            'kind' => 'post', 'type' => 'post', 'canonical_path' => '/blog/a-post/', 'h1' => 'A Post',
            'published_at' => '2025-10-25 06:48:00', 'body_html' => '<p>Hello</p>',
        ]);
        $this->getRaw('/blog/a-post/')->assertOk()->assertSee('25 October 2025')->assertSee('Quick Booking');
    }

    public function test_blog_index_lists_posts_newest_first(): void
    {
        \App\Models\Page::factory()->create(['type' => 'blog_index', 'canonical_path' => '/blog/', 'h1' => 'Blog']);
        \App\Models\Page::factory()->create(['kind' => 'post', 'type' => 'post', 'canonical_path' => '/blog/old/', 'h1' => 'Old post', 'published_at' => '2025-01-01']);
        \App\Models\Page::factory()->create(['kind' => 'post', 'type' => 'post', 'canonical_path' => '/blog/new/', 'h1' => 'New post', 'published_at' => '2025-06-01']);
        $this->getRaw('/blog/')->assertOk()->assertSeeInOrder(['New post', 'Old post']);
    }
}

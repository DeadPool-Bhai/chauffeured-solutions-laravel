<?php

namespace Tests\Unit;

use App\Support\HtmlCleaner;
use PHPUnit\Framework\TestCase;

class HtmlCleanerTest extends TestCase
{
    public function test_strips_widget_cruft_and_keeps_content(): void
    {
        $html = '<div class="x"><svg><path d="M0"/></svg><!-- c --><a href="#"><h3>Title</h3><p>Body <b>bold</b></p></a>'
            .'<a href="https://chauffeuredsolutions.com.au/booking/" class="btn" style="x">Book</a>'
            .'<a href="#elementor-action%3Aaction">Get a Free Quote</a>'
            .'<a href="https://book.mylimobiz.com/v4/chausol" data-ores-widget="quickres">Online Reservations</a>'
            .'<ul><li aria-level="1"><span>One</span></li><li></li></ul></div>';

        $out = HtmlCleaner::clean($html, 'https://chauffeuredsolutions.com.au');

        $this->assertStringContainsString('<h3>Title</h3>', $out);
        $this->assertStringContainsString('<p>Body <b>bold</b></p>', $out);
        $this->assertStringContainsString('href="/booking/"', $out);
        $this->assertStringContainsString('<ul><li>One</li></ul>', $out);
        $this->assertStringNotContainsString('svg', $out);
        $this->assertStringNotContainsString('class=', $out);
        $this->assertStringNotContainsString('Get a Free Quote', $out);
        $this->assertStringNotContainsString('Online Reservations', $out);
    }

    public function test_accordion_toggles_become_headings(): void
    {
        $html = '<a href="#c1" data-ekit-toggle="collapse">  How far ahead?  <i></i></a><p>24 to 48 hours.</p>';

        $this->assertSame("<h3>How far ahead?</h3>\n<p>24 to 48 hours.</p>", HtmlCleaner::clean($html));
    }

    public function test_loose_widget_text_becomes_a_paragraph(): void
    {
        $this->assertSame("<p>About</p>\n<h2>Heading</h2>", HtmlCleaner::clean('About <h2>Heading</h2>'));
    }

    public function test_relativise_handles_site_variants(): void
    {
        $this->assertSame('/a/', HtmlCleaner::relativise('https://chauffeuredsolutions.com.au/a/', 'https://chauffeuredsolutions.com.au'));
        $this->assertSame('/', HtmlCleaner::relativise('https://chauffeuredsolutions.com.au', 'https://chauffeuredsolutions.com.au'));
        $this->assertSame('https://example.com/a', HtmlCleaner::relativise('https://example.com/a', 'https://chauffeuredsolutions.com.au'));
    }

    public function test_shortcodes_images_and_booking_tokens(): void
    {
        $html = '<p>[fluentform id="4"] Best chauffeur</p><img src="https://chauffeuredsolutions.com.au/wp-content/uploads/a.jpg" alt="A" class="x">'
            .'<p><a href="https://book.mylimobiz.com/v4/(S(abc123))/chausol">Quick Booking</a></p>';

        $out = HtmlCleaner::clean($html, 'https://chauffeuredsolutions.com.au');

        $this->assertStringContainsString("[fluentform id=\"4\"]<p>Best chauffeur</p>", $out);
        $this->assertStringContainsString('src="/wp-content/uploads/a.jpg"', $out);
        $this->assertStringContainsString('loading="lazy"', $out);
        $this->assertStringContainsString('href="https://book.mylimobiz.com/v4/chausol"', $out);
    }

    public function test_trustindex_markup_becomes_a_shortcode_token(): void
    {
        $html = '<h2>Testimonials</h2><link rel="x"><pre><img src="https://cdn.trustindex.io/s.svg"> Rohit Kapoor Great</pre><h2>FAQs</h2>';

        $this->assertSame("<h2>Testimonials</h2>\n[trustindex]<h2>FAQs</h2>", HtmlCleaner::clean($html));
    }
}

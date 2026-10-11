<?php

namespace Tests\Feature;

use App\Console\Commands\QaCrawl;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QaCrawlTest extends TestCase
{
    use RefreshDatabase;

    private function html(string $title, string $extra = ''): string
    {
        return '<html><head><title>'.$title.'</title><link rel="canonical" href="https://x.com/a/"><meta name="description" content="d">'
            .'<script type="application/ld+json">{"@type":"Thing"}</script></head><body><main><h1>Hi</h1><h2>a</h2><p>one two three</p>'.$extra.'</main></body></html>';
    }

    public function test_parse_extracts_the_seo_fields(): void
    {
        $p = QaCrawl::parse($this->html('T &amp; Co'));
        $this->assertSame('T & Co', $p['title']);
        $this->assertSame('Hi', $p['h1']);
        $this->assertSame(1, $p['h2']);
        $this->assertNotSame('', $p['schema']);
    }

    public function test_command_flags_a_title_difference_and_passes_identical_pages(): void
    {
        Page::factory()->create(['canonical_path' => '/a/']);
        Page::factory()->create(['canonical_path' => '/b/']);
        Http::fake([
            'live.test/a/' => Http::response($this->html('Same')),
            'stage.test/a/' => Http::response($this->html('Same')),
            'live.test/b/' => Http::response($this->html('Old')),
            'stage.test/b/' => Http::response($this->html('New')),
        ]);
        $this->artisan('qa:crawl', ['staging' => 'https://stage.test', '--live' => 'https://live.test', '--out' => 'storage/app/qa-test.csv'])
            ->expectsOutputToContain('HARD /b/: title')
            ->assertFailed();
        @unlink(base_path('storage/app/qa-test.csv'));
    }
}

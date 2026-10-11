<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_is_untouched(): void
    {
        Page::factory()->create(['canonical_path' => '/x/']);
        $r = $this->getRaw('/x/')->assertOk();
        $this->assertNull($r->headers->get('X-Robots-Tag'));
        $this->get('/robots.txt')->assertSee('Sitemap:');
    }

    public function test_staging_blocks_indexing_and_asks_for_login(): void
    {
        config(['site.staging' => true, 'site.staging_auth' => 'qa:secret']);
        Page::factory()->create(['canonical_path' => '/x/']);

        $this->getRaw('/x/')->assertStatus(401)->assertHeader('WWW-Authenticate');
        $r = $this->getRaw('/x/', ['PHP_AUTH_USER' => 'qa', 'PHP_AUTH_PW' => 'secret'])->assertOk();
        $this->assertSame('noindex, nofollow', $r->headers->get('X-Robots-Tag'));
        $this->getRaw('/robots.txt', ['PHP_AUTH_USER' => 'qa', 'PHP_AUTH_PW' => 'secret'])->assertSee('Disallow: /', false)->assertDontSee('Sitemap');
        $this->get('/up')->assertOk();
    }
}

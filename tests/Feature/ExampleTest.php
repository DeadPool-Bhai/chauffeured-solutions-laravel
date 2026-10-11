<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_is_served_from_the_database(): void
    {
        Page::factory()->create(['canonical_path' => '/', 'seo_title' => 'Home title']);

        $this->get('/')->assertOk()->assertSee('Home title');
    }
}

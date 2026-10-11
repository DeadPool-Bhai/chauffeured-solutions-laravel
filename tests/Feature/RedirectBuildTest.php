<?php

namespace Tests\Feature;

use App\Services\WordPressImporter;
use Tests\TestCase;

class RedirectBuildTest extends TestCase
{
    public function test_chains_are_flattened_and_targets_use_canonical_form(): void
    {
        $raw = [
            ['from' => 'airport-transfers/', 'to' => '/sydney-airport-luxury-transfer-offer/', 'status' => 301],
            ['from' => 'sydney-airport-luxury-transfer-offer/', 'to' => '/airport-transfers-sydney/', 'status' => 301],
            ['from' => 'corporate-cars/hotel-transfers/', 'to' => '/hotel-transfers/', 'status' => 301],
            ['from' => 'favicon.ico', 'to' => '/wp-content/uploads/favicon.png', 'status' => 410],
        ];
        $pages = [
            '/airport-transfers-sydney' => '/airport-transfers-sydney/',
            '/hotel-transfers' => '/hotel-transfers',
        ];

        $out = collect((new WordPressImporter)->buildRedirects($raw, $pages))->keyBy('from_path');

        $this->assertSame('/airport-transfers-sydney/', $out['/airport-transfers']['to_path']);
        $this->assertSame('/hotel-transfers', $out['/corporate-cars/hotel-transfers']['to_path']);
        $this->assertNull($out['/favicon.ico']['to_path']);
        $this->assertSame(410, $out['/favicon.ico']['status_code']);
    }
}

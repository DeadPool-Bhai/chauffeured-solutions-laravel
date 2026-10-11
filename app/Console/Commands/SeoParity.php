<?php

namespace App\Console\Commands;

use App\Http\Controllers\SitemapController;
use App\Models\Redirect;
use App\Support\UrlPath;
use Illuminate\Console\Command;

class SeoParity extends Command
{
    protected $signature = 'seo:parity';

    protected $description = 'Compare the generated sitemap URL sets with the live WordPress sitemaps';

    public function handle(SitemapController $sitemaps): int
    {
        $live = json_decode(file_get_contents(database_path('seeders/data/live_sitemap.json')), true);
        $bad = 0;

        foreach (['page', 'post'] as $kind) {
            $new = $sitemaps->pages($kind)->pluck('canonical_path')->all();
            $old = $live[$kind];
            $missing = array_values(array_diff($old, $new));
            $extra = array_values(array_diff($new, $old));
            $this->info(strtoupper($kind).": live ".count($old).", new ".count($new));
            $redirected = Redirect::where('is_active', true)->pluck('from_path')->flip();
            foreach ($missing as $m) {
                if (isset($redirected[UrlPath::redirectKey($m)])) {
                    $this->line("  dropped on purpose (redirects): {$m}");
                    $bad--;

                    continue;
                }
                $this->warn("  only on live: {$m}");
            }
            foreach ($extra as $e) {
                $this->warn("  only in new:  {$e}");
            }
            $bad += count($missing) + count($extra);
        }

        return $bad ? self::FAILURE : self::SUCCESS;
    }
}

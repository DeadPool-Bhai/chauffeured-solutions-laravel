<?php

namespace App\Console\Commands;

use App\Services\WordPressImporter;
use App\Support\UrlPath;
use Illuminate\Console\Command;

class ImportWordpress extends Command
{
    protected $signature = 'import:wordpress
        {xml : Path to the WXR export}
        {--redirects=database/seeders/data/redirects_raw.json : Raw Rank Math rules}
        {--snapshot= : JSON of rendered titles/descriptions keyed by canonical path}
        {--dry-run : Parse and report without writing}';

    protected $description = 'Import published WordPress pages, posts and redirects';

    public function handle(WordPressImporter $importer): int
    {
        $snapshot = $this->option('snapshot') ? json_decode(file_get_contents($this->option('snapshot')), true) : [];
        $pages = $importer->parsePages($this->argument('xml'), $snapshot ?? []);

        $pathByKey = [];
        foreach ($pages as $p) {
            $pathByKey[UrlPath::redirectKey($p['canonical_path'])] = $p['canonical_path'];
            if ($p['_custom_permalink']) {
                // Alias used by old rules: only if it does not already point at a different live page.
                $pathByKey[UrlPath::redirectKey($p['_custom_permalink'])] ??= $p['canonical_path'];
            }
        }

        $raw = json_decode(file_get_contents($this->option('redirects')), true);
        $overrides = is_file($f = database_path('seeders/data/redirect_overrides.json')) ? json_decode(file_get_contents($f), true) : [];
        foreach ($raw as &$rule) {
            if (isset($overrides[$rule['from']])) {
                $rule['to'] = $overrides[$rule['from']];
            }
        }
        unset($rule);
        $redirects = $importer->buildRedirects($raw, $pathByKey);

        $this->table(['Metric', 'Count'], [
            ['Pages', count(array_filter($pages, fn ($p) => $p['kind'] === 'page'))],
            ['Posts', count(array_filter($pages, fn ($p) => $p['kind'] === 'post'))],
            ['noindex', count(array_filter($pages, fn ($p) => $p['robots']))],
            ['Need rendered title/description snapshot', count(array_filter($pages, fn ($p) => $p['_needs_snapshot']))],
            ['Redirect rules', count($redirects)],
        ]);
        foreach ($importer->notes as $n) {
            $this->warn($n);
        }

        if ($this->option('dry-run')) {
            return self::SUCCESS;
        }

        $importer->persist($pages, $redirects);
        $this->info('Imported.');

        return self::SUCCESS;
    }
}

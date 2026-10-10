<?php

namespace App\Support;

/** Replaces the few WordPress shortcodes that survive in page content. */
class Shortcodes
{
    public static function expand(string $html): string
    {
        return preg_replace_callback('#\[(fluentform|trustindex|\d+)([^\]]*)\]#i', function ($m) {
            return match (strtolower($m[1])) {
                'fluentform' => view('components.quote-form')->render(),
                'trustindex' => view('components.reviews')->render(),
                default => '', // Elementor template includes, e.g. [8226]
            };
        }, $html);
    }

    /** Strip every shortcode, for templates that place those blocks themselves. */
    public static function strip(string $html): string
    {
        return trim(preg_replace('#\[(?:fluentform|trustindex|\d+)[^\]]*\]#i', '', $html));
    }
}

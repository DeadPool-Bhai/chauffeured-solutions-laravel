<?php

namespace App\Support;

/** Replaces the few WordPress shortcodes that survive in page content. */
class Shortcodes
{
    public static function expand(string $html): string
    {
        return preg_replace_callback('#\[(fluentform|trustindex|\d+)([^\]]*)\]#i', function ($m) {
            return match (strtolower($m[1])) {
                'fluentform' => self::form($m[2]),
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

    private static function form(string $attrs): string
    {
        $id = preg_match('/id=["\']?(\d+)/', $attrs, $x) ? $x[1] : '4';

        return match ($id) {
            '9' => view('components.dynamic-form', ['form' => 'wedding-quote'])->render(),
            '7' => view('components.dynamic-form', ['form' => 'wedding-booking'])->render(),
            default => view('components.quote-form', ['ctx' => 'page'])->render(),
        };
    }
}

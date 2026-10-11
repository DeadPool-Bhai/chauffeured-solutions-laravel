<?php

namespace App\Support;

/**
 * Path helpers for the slash / no-slash rules.
 *
 * The live WordPress site uses a trailing slash on most URLs and none on 13 of them.
 * Each page stores its canonical path exactly as live, so these helpers never decide
 * which form is right. They only convert between forms.
 */
class UrlPath
{
    /** Request path with a leading slash and the query string removed. */
    public static function fromRequestPath(string $path): string
    {
        $path = '/'.ltrim($path, '/');

        return $path === '' ? '/' : $path;
    }

    /** The same path with the trailing slash toggled. The root has no twin. */
    public static function twin(string $path): ?string
    {
        if ($path === '/') {
            return null;
        }

        return str_ends_with($path, '/') ? rtrim($path, '/') : $path.'/';
    }

    /** Key used for redirect rules: lower case, leading slash, no trailing slash. */
    public static function redirectKey(string $path): string
    {
        $path = '/'.trim($path, '/');

        return mb_strtolower($path === '/' ? '/' : rtrim($path, '/'));
    }
}

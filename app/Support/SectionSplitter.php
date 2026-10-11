<?php

namespace App\Support;

/** Splits cleaned page HTML into blocks so templates can lay out each part. */
class SectionSplitter
{
    /**
     * @return array{intro:string, sections:list<array{title:string,html:string,slug:string}>}
     */
    public static function byH2(string $html): array
    {
        $parts = preg_split('#(?=<h2[ >])#i', $html, -1, PREG_SPLIT_NO_EMPTY);
        $intro = '';
        $sections = [];

        foreach ($parts as $part) {
            if (preg_match('#^<h2[^>]*>(.*?)</h2>(.*)$#is', $part, $m)) {
                $title = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES));
                $sections[] = ['title' => $title, 'html' => trim($m[2]), 'slug' => str($title)->slug()->toString()];
            } else {
                $intro .= $part;
            }
        }

        return ['intro' => trim($intro), 'sections' => $sections];
    }

    /**
     * Split a section into its lead-in plus h3 blocks (cards, FAQ rows).
     *
     * @return array{lead:string, items:list<array{title:string,html:string}>}
     */
    public static function byH3(string $html): array
    {
        $parts = preg_split('#(?=<h3[ >])#i', $html, -1, PREG_SPLIT_NO_EMPTY);
        $lead = '';
        $items = [];

        foreach ($parts as $part) {
            if (preg_match('#^<h3[^>]*>(.*?)</h3>(.*)$#is', $part, $m)) {
                $items[] = ['title' => trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES)), 'html' => trim($m[2])];
            } else {
                $lead .= $part;
            }
        }

        return ['lead' => trim($lead), 'items' => $items];
    }
}

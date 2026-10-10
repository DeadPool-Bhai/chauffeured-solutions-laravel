<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

/**
 * Turns Elementor's flattened post_content into clean semantic HTML.
 *
 * Keeps headings, paragraphs, lists, tables, images and real links. Drops SVG icons,
 * comments, widget hooks, anchors that only point at "#", and container wrappers.
 */
class HtmlCleaner
{
    private const UNWRAP = ['div', 'span', 'section', 'article', 'figure', 'header', 'footer', 'main', 'center', 'font'];

    private const KEEP_ATTRS = [
        'a' => ['href'],
        'img' => ['src', 'alt', 'width', 'height', 'loading'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan'],
    ];

    public static function clean(string $html, string $siteUrl = ''): string
    {
        if (trim($html) === '') {
            return '';
        }

        $doc = new DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?><div id="__root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xp = new DOMXPath($doc);
        $root = $doc->getElementById('__root') ?? $doc->documentElement;

        // Junk nodes.
        foreach ($xp->query('//svg | //script | //style | //noscript | //iframe | //form | //button | //input | //comment()') as $n) {
            $n->parentNode?->removeChild($n);
        }

        // Trustindex bakes its rendered reviews into the content. Swap each copy for a shortcode token.
        foreach (iterator_to_array($xp->query('//pre[.//img[contains(@src, "trustindex.io")]]')) as $pre) {
            $p = $doc->createElement('p', '[trustindex]');
            $pre->parentNode?->replaceChild($p, $pre);
        }
        foreach ($xp->query('//link | //meta') as $n) {
            $n->parentNode?->removeChild($n);
        }

        // Widget hooks and popup triggers.
        foreach ($xp->query('//a[@data-ores-widget or starts-with(@href, "#elementor-action")]') as $n) {
            $n->parentNode?->removeChild($n);
        }

        // Accordion toggles become real headings so FAQ questions stay semantic.
        foreach ($xp->query('//a[@data-ekit-toggle]') as $a) {
            $h = $doc->createElement('h3');
            $h->appendChild($doc->createTextNode(trim(preg_replace('/\s+/', ' ', $a->textContent))));
            $a->parentNode?->replaceChild($h, $a);
        }

        // Anchors without a real destination.
        foreach (iterator_to_array($xp->query('//a')) as $a) {
            $href = trim($a->getAttribute('href'));
            if ($href === '' || str_starts_with($href, '#') || str_starts_with(strtolower($href), 'javascript:')) {
                self::unwrap($a);
            }
        }

        // Strip attributes, unwrap containers.
        foreach (iterator_to_array($xp->query('//*')) as $el) {
            if (! $el instanceof DOMElement || $el === $root) {
                continue;
            }
            $tag = strtolower($el->tagName);
            $keep = self::KEEP_ATTRS[$tag] ?? [];
            foreach (iterator_to_array($el->attributes) as $attr) {
                if (! in_array($attr->name, $keep, true)) {
                    $el->removeAttribute($attr->name);
                }
            }
            if ($tag === 'a') {
                $href = preg_replace('#/\(S\([a-z0-9]+\)\)#i', '', $el->getAttribute('href')); // Limo Anywhere cookieless session token
                $el->setAttribute('href', $siteUrl !== '' ? self::relativise($href, $siteUrl) : $href);
            }
            if ($tag === 'img') {
                if ($siteUrl !== '') {
                    $el->setAttribute('src', self::relativise($el->getAttribute('src'), $siteUrl));
                }
                $el->setAttribute('loading', 'lazy');
            }
        }
        foreach (iterator_to_array($xp->query('//*')) as $el) {
            if ($el instanceof DOMElement && $el !== $root && in_array(strtolower($el->tagName), self::UNWRAP, true)) {
                self::unwrap($el);
            }
        }

        self::wrapLooseText($doc, $root);
        self::removeEmpty($doc, $root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }
        $out = preg_replace('/>\s+</', '><', $out);
        $out = preg_replace('/\s+/', ' ', $out);
        // A shortcode that shares a paragraph with other text moves to its own line.
        $out = preg_replace('#<p>\s*(\[[a-z_]+[^\]]*\]|\[\d+\])\s*(.*?)</p>#i', "$1<p>$2</p>", $out);
        $out = str_replace('<p></p>', '', $out);
        $out = preg_replace('#<(p|h[1-6]|li)> #', '<$1>', $out);
        $out = preg_replace('# </(p|h[1-6]|li)>#', '</$1>', $out);

        return trim(preg_replace('#(</(?:p|h[1-6]|ul|ol|table|blockquote)>)#', "$1\n", $out));
    }

    /** Replace absolute links to the live site with root-relative ones. */
    public static function relativise(string $href, string $siteUrl): string
    {
        $site = rtrim($siteUrl, '/');
        foreach ([$site, preg_replace('#^https://#', 'http://', $site), preg_replace('#^(https?://)#', '$1www.', $site)] as $prefix) {
            if (str_starts_with($href, $prefix)) {
                $rest = substr($href, strlen($prefix));

                return $rest === '' || $rest[0] === '?' || $rest[0] === '#' ? '/'.$rest : $rest;
            }
        }

        return $href;
    }

    private static function unwrap(DOMNode $node): void
    {
        $parent = $node->parentNode;
        if (! $parent) {
            return;
        }
        while ($node->firstChild) {
            $parent->insertBefore($node->firstChild, $node);
        }
        $parent->removeChild($node);
    }

    /** Text sitting directly in the root (widget labels) becomes a paragraph. */
    private static function wrapLooseText(DOMDocument $doc, DOMElement $root): void
    {
        $inline = ['a', 'strong', 'b', 'em', 'i', 'br', 'u', 'small', 'sup', 'sub'];
        $run = [];
        $flush = function () use (&$run, $doc, $root) {
            if (! $run) {
                return;
            }
            $text = trim(implode('', array_map(fn ($n) => $n->textContent, $run)));
            if ($text !== '') {
                $p = $doc->createElement('p');
                $root->insertBefore($p, $run[0]);
                foreach ($run as $n) {
                    $p->appendChild($n);
                }
            } else {
                foreach ($run as $n) {
                    $root->removeChild($n);
                }
            }
            $run = [];
        };
        foreach (iterator_to_array($root->childNodes) as $child) {
            $isInline = $child->nodeType === XML_TEXT_NODE
                || ($child instanceof DOMElement && in_array(strtolower($child->tagName), $inline, true));
            if ($isInline) {
                $run[] = $child;
            } else {
                $flush();
            }
        }
        $flush();
    }

    private static function removeEmpty(DOMDocument $doc, DOMElement $root): void
    {
        $xp = new DOMXPath($doc);
        do {
            $removed = false;
            foreach (iterator_to_array($xp->query('//p | //li | //ul | //ol | //h1 | //h2 | //h3 | //h4 | //h5 | //h6 | //strong | //b | //em | //a | //td | //th | //tr')) as $el) {
                if (trim($el->textContent) === '' && ! $el->getElementsByTagName('img')->length) {
                    $el->parentNode?->removeChild($el);
                    $removed = true;
                }
            }
        } while ($removed);
    }
}

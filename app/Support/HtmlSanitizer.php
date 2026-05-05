<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;

class HtmlSanitizer
{
    /**
     * Tags allowed in sanitized rich-text output.
     *
     * @var list<string>
     */
    private const ALLOWED_TAGS = [
        'a', 'p', 'br', 'strong', 'em', 'b', 'i', 'u', 's', 'small', 'sub', 'sup',
        'ul', 'ol', 'li', 'blockquote', 'pre', 'code',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'img', 'figure', 'figcaption',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption',
        'span', 'div', 'hr', 'section', 'article',
    ];

    /**
     * Per-tag attribute allowlist. The '*' key applies to every allowed tag.
     *
     * @var array<string, list<string>>
     */
    private const ALLOWED_ATTRS = [
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        'th' => ['colspan', 'rowspan', 'scope'],
        'td' => ['colspan', 'rowspan'],
        '*' => ['class', 'id', 'lang', 'dir'],
    ];

    /**
     * URL schemes allowed in href/src attributes.
     *
     * @var list<string>
     */
    private const SAFE_URL_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    public static function sanitize(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $previousErrors = libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="__sanitizer_root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        $xpath = new DOMXPath($dom);

        foreach ($xpath->query('//script | //style | //iframe | //object | //embed | //form | //input | //button | //textarea | //select | //option | //link | //meta | //base | //svg | //math') ?: [] as $node) {
            $node->parentNode?->removeChild($node);
        }

        foreach ($xpath->query('//comment()') ?: [] as $comment) {
            $comment->parentNode?->removeChild($comment);
        }

        $elements = [];
        foreach ($xpath->query('//*') ?: [] as $el) {
            $elements[] = $el;
        }

        foreach ($elements as $el) {
            if (! $el instanceof DOMElement) {
                continue;
            }
            if ($el->getAttribute('id') === '__sanitizer_root') {
                continue;
            }

            $tag = strtolower($el->nodeName);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                self::unwrap($el);

                continue;
            }

            $allowedForTag = array_merge(
                self::ALLOWED_ATTRS[$tag] ?? [],
                self::ALLOWED_ATTRS['*'] ?? []
            );

            $attrNames = [];
            foreach ($el->attributes as $a) {
                $attrNames[] = $a->nodeName;
            }

            foreach ($attrNames as $name) {
                $lower = strtolower($name);

                if (str_starts_with($lower, 'on') || ! in_array($lower, $allowedForTag, true)) {
                    $el->removeAttribute($name);

                    continue;
                }

                if (in_array($lower, ['href', 'src'], true)) {
                    $value = trim($el->getAttribute($name));
                    if (! self::isSafeUrl($value)) {
                        $el->removeAttribute($name);
                    }
                }
            }

            if ($tag === 'a' && $el->getAttribute('target') === '_blank') {
                $el->setAttribute('rel', 'noopener noreferrer');
            }
        }

        $root = $dom->getElementById('__sanitizer_root');
        if ($root === null) {
            return '';
        }

        $out = '';
        foreach ($root->childNodes as $child) {
            $rendered = $dom->saveHTML($child);
            if ($rendered !== false) {
                $out .= $rendered;
            }
        }

        return $out;
    }

    private static function unwrap(DOMElement $el): void
    {
        $parent = $el->parentNode;
        if ($parent === null) {
            return;
        }
        while ($el->firstChild) {
            $parent->insertBefore($el->firstChild, $el);
        }
        $parent->removeChild($el);
    }

    private static function isSafeUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }
        if ($url[0] === '/' || $url[0] === '#') {
            return true;
        }
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if ($scheme === '') {
            return true;
        }

        return in_array($scheme, self::SAFE_URL_SCHEMES, true);
    }
}

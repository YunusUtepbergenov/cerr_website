<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * The chapter list of a page body: every <h2> gets an id (chapter-1,
 * chapter-2, … unless the editor set one) so an in-page table of contents
 * can link to it. Numbers are used because slugs drop Uzbek letters.
 */
class PageOutline
{
    /**
     * @param  list<array{id: string, title: string}>  $chapters
     */
    private function __construct(public readonly string $html, public readonly array $chapters) {}

    public static function build(?string $html): self
    {
        $html = (string) $html;

        if (stripos($html, '<h2') === false) {
            return new self($html, []);
        }

        $previousErrors = libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="__outline_root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        $chapters = [];
        $used = [];
        foreach ((new DOMXPath($dom))->query('//h2') ?: [] as $index => $heading) {
            if (! $heading instanceof DOMElement) {
                continue;
            }
            $title = trim((string) preg_replace('/\s+/u', ' ', $heading->textContent));
            if ($title === '') {
                continue;
            }

            $id = $heading->getAttribute('id');
            if ($id === '') {
                $id = 'chapter-'.(count($chapters) + 1);
            }
            $candidate = $id;
            for ($n = 2; isset($used[$candidate]); $n++) {
                $candidate = $id.'-'.$n;
            }
            $used[$candidate] = true;
            $heading->setAttribute('id', $candidate);

            $chapters[] = ['id' => $candidate, 'title' => $title];
        }

        $root = $dom->getElementById('__outline_root');
        if ($root === null) {
            return new self($html, []);
        }

        $out = '';
        foreach ($root->childNodes as $child) {
            $rendered = $dom->saveHTML($child);
            if ($rendered !== false) {
                $out .= $rendered;
            }
        }

        return new self($out, $chapters);
    }
}

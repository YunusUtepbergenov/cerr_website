<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

/**
 * Post-processes sanitized article HTML so tables pasted from Word render as
 * proper data tables: a real header row, compact cells, numeric alignment,
 * coloured trend arrows and a horizontally scrollable wrapper.
 */
class ArticleTables
{
    private const WRAPPER_CLASS = 'article-table';

    private const NUMERIC_PATTERN = '/^[↑↓▲▼]?\s*[+\-−–]?\d[\d\s.,]*%?$/u';

    public static function enhance(?string $html): string
    {
        if ($html === null || trim($html) === '' || stripos($html, '<table') === false) {
            return (string) $html;
        }

        $previousErrors = libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="__tables_root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        $xpath = new DOMXPath($dom);

        foreach ($xpath->query('//table') ?: [] as $table) {
            if ($table instanceof DOMElement) {
                self::enhanceTable($dom, $xpath, $table);
            }
        }

        $root = $dom->getElementById('__tables_root');
        if ($root === null) {
            return (string) $html;
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

    private static function enhanceTable(DOMDocument $dom, DOMXPath $xpath, DOMElement $table): void
    {
        foreach ($xpath->query('.//td | .//th', $table) ?: [] as $cell) {
            if ($cell instanceof DOMElement) {
                self::flattenParagraphs($dom, $xpath, $cell);
            }
        }

        self::promoteHeaderRow($dom, $xpath, $table);

        /** @var list<DOMElement> $bodyRows */
        $bodyRows = [];
        foreach ($xpath->query('./tbody/tr | ./tr', $table) ?: [] as $row) {
            if ($row instanceof DOMElement) {
                $bodyRows[] = $row;
            }
        }

        $numericColumns = [];
        foreach ($bodyRows as $row) {
            $cells = self::cells($row);
            $hasNumeric = false;

            foreach ($cells as $index => $cell) {
                if (self::isNumeric($cell)) {
                    self::addClass($cell, 'num');
                    self::markTrend($dom, $cell);
                    $numericColumns[$index] = ($numericColumns[$index] ?? 0) + 1;
                    $hasNumeric = true;
                }
            }

            if ($hasNumeric && isset($cells[0]) && ! self::isNumeric($cells[0])) {
                self::addClass($cells[0], 'label');
            }
        }

        $rowCount = max(1, count($bodyRows));
        foreach ($xpath->query('./thead/tr[1]/th', $table) ?: [] as $index => $header) {
            if ($header instanceof DOMElement && ($numericColumns[$index] ?? 0) * 2 >= $rowCount) {
                self::addClass($header, 'num');
            }
        }

        self::wrap($dom, $table);
    }

    /**
     * Word wraps every cell value in <p>; join those paragraphs into one flowing
     * cell so "Жиззах" / "вилояти" no longer renders as two paragraphs.
     */
    private static function flattenParagraphs(DOMDocument $dom, DOMXPath $xpath, DOMElement $cell): void
    {
        $paragraphs = [];
        foreach ($xpath->query('./p', $cell) ?: [] as $p) {
            if ($p instanceof DOMElement) {
                $paragraphs[] = $p;
            }
        }

        foreach ($paragraphs as $position => $p) {
            if ($position > 0) {
                $cell->insertBefore($dom->createTextNode(' '), $p);
            }
            while ($p->firstChild) {
                $cell->insertBefore($p->firstChild, $p);
            }
            $cell->removeChild($p);
        }
    }

    /**
     * Tables pasted from Word have no <thead>; their first row is the header.
     */
    private static function promoteHeaderRow(DOMDocument $dom, DOMXPath $xpath, DOMElement $table): void
    {
        if (($xpath->query('./thead', $table)?->length ?? 0) > 0) {
            return;
        }

        $rows = $xpath->query('./tbody/tr | ./tr', $table);
        if (! $rows || $rows->length < 2) {
            return;
        }

        $first = $rows->item(0);
        if (! $first instanceof DOMElement) {
            return;
        }

        foreach (self::cells($first) as $cell) {
            if ($cell->nodeName === 'th') {
                $cell->setAttribute('scope', 'col');

                continue;
            }
            $th = $dom->createElement('th');
            $th->setAttribute('scope', 'col');
            foreach (['colspan', 'rowspan', 'class', 'style'] as $attr) {
                if ($cell->hasAttribute($attr)) {
                    $th->setAttribute($attr, $cell->getAttribute($attr));
                }
            }
            while ($cell->firstChild) {
                $th->appendChild($cell->firstChild);
            }
            $cell->parentNode?->replaceChild($th, $cell);
        }

        $thead = $dom->createElement('thead');
        $thead->appendChild($first);
        $table->insertBefore($thead, $table->firstChild);
    }

    private static function markTrend(DOMDocument $dom, DOMElement $cell): void
    {
        $text = trim($cell->textContent);
        $arrow = mb_substr($text, 0, 1);
        $direction = match ($arrow) {
            '↑', '▲' => 'up',
            '↓', '▼' => 'down',
            default => null,
        };
        if ($direction === null) {
            return;
        }

        self::addClass($cell, $direction);

        $first = self::firstTextNode($cell);
        if ($first === null) {
            return;
        }

        $wrapper = $first->parentNode;
        if ($wrapper instanceof DOMElement && $wrapper->getAttribute('class') === 'trend') {
            return;
        }

        $value = ltrim($first->nodeValue ?? '');
        if (! str_starts_with($value, $arrow)) {
            return;
        }

        $span = $dom->createElement('span', $arrow);
        $span->setAttribute('class', 'trend');
        $span->setAttribute('aria-hidden', 'true');
        $first->nodeValue = ltrim(mb_substr($value, 1));
        $first->parentNode?->insertBefore($span, $first);
    }

    private static function wrap(DOMDocument $dom, DOMElement $table): void
    {
        $parent = $table->parentNode;
        if ($parent instanceof DOMElement && $parent->getAttribute('class') === self::WRAPPER_CLASS) {
            return;
        }

        $wrapper = $dom->createElement('div');
        $wrapper->setAttribute('class', self::WRAPPER_CLASS);
        $parent?->replaceChild($wrapper, $table);
        $wrapper->appendChild($table);
    }

    /** @return list<DOMElement> */
    private static function cells(DOMElement $row): array
    {
        $cells = [];
        foreach ($row->childNodes as $child) {
            if ($child instanceof DOMElement && in_array($child->nodeName, ['td', 'th'], true)) {
                $cells[] = $child;
            }
        }

        return $cells;
    }

    private static function isNumeric(DOMElement $cell): bool
    {
        $text = trim(preg_replace('/\s+/u', ' ', $cell->textContent) ?? '');

        return $text !== '' && preg_match(self::NUMERIC_PATTERN, $text) === 1;
    }

    private static function addClass(DOMElement $el, string $class): void
    {
        $classes = preg_split('/\s+/', trim($el->getAttribute('class'))) ?: [];
        if (! in_array($class, $classes, true)) {
            $classes[] = $class;
        }
        $el->setAttribute('class', trim(implode(' ', $classes)));
    }

    private static function firstTextNode(DOMNode $node): ?DOMNode
    {
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE && trim($child->nodeValue ?? '') !== '') {
                return $child;
            }
            if ($child instanceof DOMElement) {
                $found = self::firstTextNode($child);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }
}

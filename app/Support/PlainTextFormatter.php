<?php

namespace App\Support;

/**
 * Turns legacy plain-text page bodies into simple HTML.
 *
 * Pages imported before the rich-text editor hold raw text: paragraphs
 * separated by blank lines, "- " list items, short unpunctuated lines used
 * as headings and bare URLs. Rendered as-is the newlines collapse into one
 * block, so this builds <p>, <h2>, <ul> and <a> out of those conventions.
 */
class PlainTextFormatter
{
    private const HEADING_MAX_LENGTH = 80;

    private const URL_PATTERN = '~https?://[^\s<>()«»"“”]+~u';

    /**
     * Whether the text already carries block-level markup and needs no conversion.
     */
    public static function looksLikeHtml(string $text): bool
    {
        return preg_match('/<(p|div|ul|ol|li|h[1-6]|table|blockquote|br|figure|section|article|pre)\b/i', $text) === 1;
    }

    public static function toHtml(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        /** @var list<string> $blocks */
        $blocks = [];

        foreach (preg_split('/\n[ \t]*\n+/', $text) ?: [] as $raw) {
            if (trim($raw) === '') {
                continue;
            }

            $joined = trim((string) preg_replace('/\s*\n\s*/', ' ', $raw));
            $startsWithUrl = self::startsWithUrl($joined);

            if ($blocks !== [] && ($startsWithUrl || preg_match('/^[ \t]/', $raw) === 1)) {
                if ($startsWithUrl) {
                    $joined = (string) preg_replace('~^(https?://\S+)~u', '($1)', $joined, 1);
                }
                $blocks[array_key_last($blocks)] .= ' '.$joined;

                continue;
            }

            $blocks[] = $joined;
        }

        $html = [];
        $inList = false;
        foreach ($blocks as $block) {
            if (preg_match('/^[-–—•]\s+(.*)$/su', $block, $m) === 1) {
                $type = 'li';
                $block = trim($m[1]);
            } else {
                $type = self::looksLikeHeading($block) ? 'h2' : 'p';
            }

            if ($type === 'li' && ! $inList) {
                $html[] = '<ul>';
                $inList = true;
            } elseif ($type !== 'li' && $inList) {
                $html[] = '</ul>';
                $inList = false;
            }
            $html[] = '<'.$type.'>'.self::inline($block).'</'.$type.'>';
        }
        if ($inList) {
            $html[] = '</ul>';
        }

        return implode("\n", $html);
    }

    private static function looksLikeHeading(string $text): bool
    {
        return mb_strlen($text) <= self::HEADING_MAX_LENGTH
            && preg_match('/[.:;!?,]$/u', $text) !== 1
            && preg_match(self::URL_PATTERN, $text) !== 1
            && preg_match('/^\p{Lu}/u', $text) === 1;
    }

    private static function startsWithUrl(string $text): bool
    {
        return preg_match('~^https?://~i', $text) === 1;
    }

    /**
     * Escape text and turn bare URLs into links labelled with their host,
     * in parentheses unless the text already put them there.
     */
    private static function inline(string $text): string
    {
        $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return (string) preg_replace_callback(self::URL_PATTERN, function (array $m) use ($escaped): string {
            [$match, $offset] = $m[0];
            $url = rtrim($match, '.,;:');
            $trailing = substr($match, strlen($url));
            $host = (string) parse_url(html_entity_decode($url, ENT_QUOTES, 'UTF-8'), PHP_URL_HOST);
            $label = $host !== '' ? (string) preg_replace('/^www\./', '', $host) : $url;
            $link = '<a href="'.$url.'" target="_blank" rel="noopener noreferrer">'.$label.'</a>';
            $inParentheses = $offset > 0 && $escaped[$offset - 1] === '(';

            return ($inParentheses ? $link : '('.$link.')').$trailing;
        }, $escaped, -1, $count, PREG_OFFSET_CAPTURE);
    }
}

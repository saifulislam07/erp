<?php

namespace App\Support;

/**
 * Cleans rich-text submitted from the editor before it is stored.
 *
 * Descriptions are rendered unescaped on the product page and in the client
 * portal, so anything a lower-privileged user types would otherwise run in an
 * administrator's browser. This keeps the formatting tags the editor produces
 * and drops everything that can execute.
 */
class HtmlSanitizer
{
    /**
     * Tags the editor's toolbar can produce.
     */
    private const ALLOWED_TAGS = '<p><br><b><strong><i><em><u><s><ul><ol><li>'
        .'<h1><h2><h3><h4><h5><h6><blockquote><pre><code><hr>'
        .'<a><table><thead><tbody><tfoot><tr><th><td><span><div>';

    public static function clean(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        // Remove whole elements that carry executable content, including their
        // contents — stripping the tag alone would leave the script body as text.
        $html = preg_replace(
            '~<\s*(script|style|iframe|object|embed|form|link|meta)\b[^>]*>.*?<\s*/\s*\1\s*>~is',
            '',
            $html
        ) ?? '';

        $html = preg_replace('~<\s*(script|style|iframe|object|embed|form|link|meta)\b[^>]*/?>~i', '', $html) ?? '';

        $html = strip_tags($html, self::ALLOWED_TAGS);

        // Event handlers (onclick=…) and javascript:/data: URLs on the tags
        // that survived.
        $html = preg_replace('~\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)~i', '', $html) ?? '';
        $html = preg_replace('~\s(href|src)\s*=\s*("|\')?\s*(javascript|data|vbscript):[^"\'>]*("|\')?~i', '', $html) ?? '';

        $html = trim($html);

        // An editor left untouched still submits an empty paragraph.
        return in_array(strtolower($html), ['', '<p><br></p>', '<p></p>', '<br>'], true) ? null : $html;
    }
}

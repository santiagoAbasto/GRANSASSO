<?php

namespace App\Traits;

/**
 * Sanitiza HTML de campos rich-text antes de persistirlos.
 *
 * Elimina:
 *  - Atributos data-* (data-start, data-end, data-is-last-node, etc.)
 *  - Atributos style="" vacíos
 *  - Espacios en blanco excesivos alrededor de etiquetas
 */
trait SanitizesHtml
{
    /**
     * Tags HTML permitidas para contenido rich-text.
     */
    protected array $allowedHtmlTags = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li',
        'blockquote', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'a',
    ];

    /**
     * Limpia un string HTML de atributos no deseados.
     */
    protected function sanitizeHtml(?string $html): string
    {
        if (empty(trim((string) $html))) {
            return '';
        }

        $html = $this->normalizeRichTextParagraphs((string) $html);

        $html = preg_replace('#<(script|style|iframe|object|embed|form|input|button|textarea|select|option|link|meta)[^>]*>.*?</\1>#is', '', $html);
        $html = preg_replace('#<(script|style|iframe|object|embed|form|input|button|textarea|select|option|link|meta)[^>]*/?>#is', '', $html);

        $allowed = '<' . implode('><', $this->allowedHtmlTags) . '>';
        $html = strip_tags($html, $allowed);

        // Eliminar atributos peligrosos y conservar solo href/target/rel en links.
        $html = preg_replace_callback('/<([a-z0-9]+)([^>]*)>/i', function (array $matches): string {
            $tag = strtolower($matches[1]);
            $attrs = $matches[2] ?? '';

            if ($tag !== 'a') {
                return "<{$tag}>";
            }

            preg_match_all('/([a-z0-9_:-]+)\s*=\s*("|\')(.*?)\2/i', $attrs, $attrMatches, PREG_SET_ORDER);

            $safeAttrs = [];

            foreach ($attrMatches as $attrMatch) {
                $name = strtolower($attrMatch[1]);
                $value = trim($attrMatch[3]);

                if (str_starts_with($name, 'on') || str_starts_with($name, 'data-') || $name === 'style') {
                    continue;
                }

                if ($name === 'href') {
                    $href = $this->sanitizeUrl($value, allowRelative: true);
                    if ($href !== null) {
                        $safeAttrs[] = 'href="' . e($href) . '"';
                    }
                    continue;
                }

                if ($name === 'target' && in_array($value, ['_blank', '_self'], true)) {
                    $safeAttrs[] = 'target="' . e($value) . '"';
                    continue;
                }

                if ($name === 'rel') {
                    $safeAttrs[] = 'rel="noopener noreferrer"';
                }
            }

            if (str_contains(implode(' ', $safeAttrs), 'target="_blank"') && ! str_contains(implode(' ', $safeAttrs), 'rel=')) {
                $safeAttrs[] = 'rel="noopener noreferrer"';
            }

            return '<a' . (empty($safeAttrs) ? '' : ' ' . implode(' ', $safeAttrs)) . '>';
        }, $html);

        // Colapsar múltiples espacios dentro de etiquetas
        $html = preg_replace('/(<[^>]+?)  +/s', '$1 ', $html);

        return trim($html);
    }

    protected function normalizeRichTextParagraphs(string $html): string
    {
        // El editor rich-text suele emitir <div> por cada Enter.
        // Los normalizamos a párrafos antes de reparar la estructura.
        $html = preg_replace('/<div\b[^>]*>\s*(<br\s*\/?>)?\s*<\/div>/i', '<p><br></p>', $html) ?? $html;
        $html = preg_replace('/<div\b[^>]*>/i', '<p>', $html) ?? $html;
        $html = preg_replace('/<\/div>/i', '</p>', $html) ?? $html;

        if (! class_exists(\DOMDocument::class)) {
            return $html;
        }

        $document = new \DOMDocument('1.0', 'UTF-8');
        $wrapperId = 'rich-text-root';

        libxml_use_internal_errors(true);
        $loaded = $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="' . $wrapperId . '">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        if (! $loaded) {
            return $html;
        }

        $wrapper = $document->getElementById($wrapperId);
        if (! $wrapper) {
            return $html;
        }

        $blockTags = ['p', 'ul', 'ol', 'blockquote', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        $childNodes = [];
        foreach ($wrapper->childNodes as $childNode) {
            $childNodes[] = $childNode;
        }

        foreach ($childNodes as $childNode) {
            $wrapper->removeChild($childNode);
        }

        $pendingParagraph = null;
        $flushPendingParagraph = function () use (&$pendingParagraph, $wrapper): void {
            if (! $pendingParagraph) {
                return;
            }

            $text = html_entity_decode((string) $pendingParagraph->textContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $hasBreak = $pendingParagraph->getElementsByTagName('br')->length > 0;

            if (trim($text) !== '' || $hasBreak) {
                $wrapper->appendChild($pendingParagraph);
            }

            $pendingParagraph = null;
        };

        foreach ($childNodes as $childNode) {
            if ($childNode->nodeType === XML_TEXT_NODE) {
                $text = html_entity_decode((string) $childNode->textContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if (trim($text) === '') {
                    continue;
                }

                $pendingParagraph ??= $document->createElement('p');
                $pendingParagraph->appendChild($document->createTextNode(trim($text)));
                continue;
            }

            if ($childNode->nodeType === XML_ELEMENT_NODE && in_array(strtolower($childNode->nodeName), $blockTags, true)) {
                $flushPendingParagraph();
                $wrapper->appendChild($childNode);
                continue;
            }

            $pendingParagraph ??= $document->createElement('p');
            $pendingParagraph->appendChild($childNode);
        }

        $flushPendingParagraph();

        $paragraphNodes = [];
        foreach ($wrapper->childNodes as $childNode) {
            if ($childNode->nodeType === XML_ELEMENT_NODE && strtolower($childNode->nodeName) === 'p') {
                $paragraphNodes[] = $childNode;
            }
        }

        if (count($paragraphNodes) >= 2) {
            $firstParagraph = $paragraphNodes[0];
            $secondParagraph = $paragraphNodes[1];

            $firstHasOnlyInlineChildren = true;
            $firstHasElementChild = false;

            foreach ($firstParagraph->childNodes as $paragraphChild) {
                if ($paragraphChild->nodeType === XML_TEXT_NODE && trim((string) $paragraphChild->textContent) !== '') {
                    $firstHasOnlyInlineChildren = false;
                    break;
                }

                if ($paragraphChild->nodeType === XML_ELEMENT_NODE) {
                    $firstHasElementChild = true;
                }
            }

            if ($firstHasOnlyInlineChildren && $firstHasElementChild) {
                $firstParagraph->appendChild($document->createTextNode(' '));

                $secondChildren = [];
                foreach ($secondParagraph->childNodes as $child) {
                    $secondChildren[] = $child;
                }

                foreach ($secondChildren as $child) {
                    $firstParagraph->appendChild($child);
                }

                $wrapper->removeChild($secondParagraph);
            }
        }

        $normalized = '';
        foreach (iterator_to_array($wrapper->childNodes) as $childNode) {
            if (
                $childNode->nodeType === XML_ELEMENT_NODE
                && strtolower($childNode->nodeName) === 'p'
            ) {
                $text = html_entity_decode((string) $childNode->textContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $hasBreak = $childNode->getElementsByTagName('br')->length > 0;

                if (trim($text) === '' && ! $hasBreak) {
                    continue;
                }
            }

            $normalized .= $document->saveHTML($childNode);
        }

        return $normalized;
    }

    protected function sanitizeUrl(?string $url, bool $allowRelative = false): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        if ($allowRelative && (str_starts_with($url, '/') || str_starts_with($url, '#'))) {
            return $url;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https', 'mailto', 'tel'], true) ? $url : null;
    }

    protected function sanitizePlainText(?string $text): string
    {
        $text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = strip_tags($text);
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    protected function sanitizeMapEmbed(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        if (! preg_match('/<iframe\b[^>]*\bsrc=(["\'])(.*?)\1[^>]*><\/iframe>/is', $html, $matches)) {
            return '';
        }

        $src = $this->sanitizeUrl($matches[2]);
        if ($src === null) {
            return '';
        }

        $host = strtolower((string) parse_url($src, PHP_URL_HOST));
        $allowedHosts = [
            'www.google.com',
            'google.com',
            'www.google.com.ar',
            'google.com.ar',
            'maps.google.com',
            'www.google.com.maps',
        ];

        $isAllowedHost = in_array($host, $allowedHosts, true) || str_ends_with($host, '.google.com');
        if (! $isAllowedHost || ! str_contains($src, '/maps')) {
            return '';
        }

        return '<iframe src="' . e($src) . '" width="100%" height="100%" style="border:0;" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="Mapa"></iframe>';
    }

    /**
     * Sanitiza un array multiidioma ['es'=>..., 'en'=>..., 'pt'=>...].
     */
    protected function sanitizeMultilang(?array $arr): array
    {
        $locales = ['es', 'en', 'pt'];
        $result  = [];
        foreach ($locales as $locale) {
            $result[$locale] = $this->sanitizeHtml($arr[$locale] ?? null);
        }
        return $result;
    }

    /**
     * Sanitiza los campos multiidioma de un bloque de datos validados.
     * Devuelve el array con los campos sobreescritos.
     */
    protected function sanitizeDataFields(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            foreach (['es', 'en', 'pt'] as $locale) {
                $key = "{$field}_{$locale}";
                if (isset($data[$key])) {
                    $data[$key] = $this->sanitizeHtml($data[$key]);
                }
            }
        }
        return $data;
    }

    protected function sanitizePlainTextFields(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            foreach (['es', 'en', 'pt'] as $locale) {
                $key = "{$field}_{$locale}";
                if (array_key_exists($key, $data)) {
                    $data[$key] = $this->sanitizePlainText($data[$key]);
                }
            }
        }

        return $data;
    }
}

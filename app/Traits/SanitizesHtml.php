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

    protected function sanitizeUrl(?string $url, bool $allowRelative = false): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        if ($allowRelative && preg_match('#^(\/|#)#', $url)) {
            return $url;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https', 'mailto', 'tel'], true) ? $url : null;
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
}

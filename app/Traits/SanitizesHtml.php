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
     * Limpia un string HTML de atributos no deseados.
     */
    protected function sanitizeHtml(?string $html): string
    {
        if (empty(trim((string) $html))) {
            return '';
        }

        // 1. Eliminar todos los atributos data-* (con o sin valor)
        $html = preg_replace('/\s+data-[a-z][a-z0-9_:-]*(?:="[^"]*")?/i', '', $html);

        // 2. Eliminar style="" vacíos
        $html = preg_replace('/\s+style="\s*"/i', '', $html);

        // 3. Colapsar múltiples espacios dentro de etiquetas
        $html = preg_replace('/(<[^>]+?)  +/s', '$1 ', $html);

        return trim($html);
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

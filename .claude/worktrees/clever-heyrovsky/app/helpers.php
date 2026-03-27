<?php

if (! function_exists('lang')) {
    /**
     * Resuelve un campo multiidioma (array JSON) al locale activo.
     * Si el locale no existe, cae a 'es'; si 'es' tampoco existe, retorna el primer valor.
     *
     * Uso en Blade: {{ lang($model->nombre) }}
     *              {!! nl2br(e(lang($model->description))) !!}
     */
    function lang(mixed $value, ?string $locale = null): string
    {
        if (is_array($value)) {
            $locale = $locale ?? app()->getLocale();

            return $value[$locale]
                ?? $value['es']
                ?? (count($value) ? reset($value) : '');
        }

        return (string) ($value ?? '');
    }
}

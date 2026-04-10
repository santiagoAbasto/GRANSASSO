<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Novedad extends Model
{
    private const TRANSLATABLE_LOCALES = ['es', 'en', 'pt'];

    protected $table = 'novedades';

    protected $appends = ['image_url'];

    protected $fillable = [
        'title',
        'category',
        'excerpt',
        'body',
        'image',
        'is_active',
    ];

    protected $casts = [
        'title'     => 'array',
        'category'  => 'array',
        'excerpt'   => 'array',
        'body'      => 'array',
        'is_active' => 'boolean',
    ];

    /** URL pública de la imagen. */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    /** Categoría en el locale activo (fallback a 'es'). */
    public function getLocalizedCategory(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $this->getNormalizedTranslationValue('category', $locale);
    }

    /** Título en el locale activo (fallback a 'es'). */
    public function getLocalizedTitle(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $this->getNormalizedTranslationValue('title', $locale);
    }

    /** Extracto en el locale activo (fallback a 'es'). */
    public function getLocalizedExcerpt(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $this->getNormalizedTranslationValue('excerpt', $locale);
    }

    /** Cuerpo en el locale activo (fallback a 'es'). */
    public function getLocalizedBody(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return self::sanitizeRichText(
            $this->getNormalizedTranslationValue('body', $locale)
        );
    }

    public function getNormalizedTranslations(string $field): array
    {
        $rawValue = $this->getRawOriginal($field);
        $value = $rawValue !== null ? $rawValue : $this->getAttribute($field);

        return self::normalizeTranslations($value);
    }

    public static function normalizeTranslations(mixed $value): array
    {
        $decoded = self::decodeNestedJson($value);

        if (! is_array($decoded)) {
            $scalar = self::normalizeScalar($decoded);

            return array_fill_keys(self::TRANSLATABLE_LOCALES, $scalar);
        }

        $translations = [];

        foreach (self::TRANSLATABLE_LOCALES as $locale) {
            $translations[$locale] = self::normalizeTranslationForLocale($decoded, $locale);
        }

        return $translations;
    }

    private function getNormalizedTranslationValue(string $field, string $locale): string
    {
        $translations = $this->getNormalizedTranslations($field);

        return $translations[$locale]
            ?? $translations['es']
            ?? $translations['en']
            ?? $translations['pt']
            ?? '';
    }

    private static function normalizeTranslationForLocale(array $decoded, string $locale): string
    {
        $candidate = $decoded[$locale]
            ?? $decoded['es']
            ?? $decoded['en']
            ?? $decoded['pt']
            ?? '';

        $normalized = self::decodeNestedJson($candidate);

        if (is_array($normalized)) {
            return self::normalizeTranslationForLocale($normalized, $locale);
        }

        return self::normalizeScalar($normalized);
    }

    private static function decodeNestedJson(mixed $value): mixed
    {
        while (is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed === '') {
                return '';
            }

            $decoded = json_decode($trimmed, true);

            if (! is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
                return $trimmed;
            }

            $value = $decoded;
        }

        return $value;
    }

    private static function normalizeScalar(mixed $value): string
    {
        return trim((string) ($value ?? ''));
    }

    private static function sanitizeRichText(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $html = preg_replace('/\sdata-[a-z0-9_-]+="[^"]*"/i', '', $html) ?? $html;
        $html = preg_replace('/\sdata-[a-z0-9_-]+=\'[^\']*\'/i', '', $html) ?? $html;

        return trim($html);
    }
}

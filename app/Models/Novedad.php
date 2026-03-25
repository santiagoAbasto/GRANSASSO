<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Novedad extends Model
{
    protected $table = 'novedades';

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
        'excerpt'   => 'array',
        'body'      => 'array',
        'is_active' => 'boolean',
    ];

    /** Título en el locale activo (fallback a 'es'). */
    public function getLocalizedTitle(string $locale = null): string
    {
        $locale ??= app()->getLocale();
        return $this->title[$locale] ?? $this->title['es'] ?? '';
    }

    /** Extracto en el locale activo (fallback a 'es'). */
    public function getLocalizedExcerpt(string $locale = null): string
    {
        $locale ??= app()->getLocale();
        return $this->excerpt[$locale] ?? $this->excerpt['es'] ?? '';
    }

    /** Cuerpo en el locale activo (fallback a 'es'). */
    public function getLocalizedBody(string $locale = null): string
    {
        $locale ??= app()->getLocale();
        return $this->body[$locale] ?? $this->body['es'] ?? '';
    }
}

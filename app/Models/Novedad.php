<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Novedad extends Model
{
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
    public function getLocalizedCategory(string $locale = null): string
    {
        $locale ??= app()->getLocale();
        return $this->category[$locale] ?? $this->category['es'] ?? '';
    }

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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'button_text',
        'button_link',
        'order',
        'is_active'
    ];

    protected $casts = [
        'title' => 'array',
        'subtitle' => 'array',
        'button_text' => 'array',
        'is_active' => 'boolean'
    ];

    /*
    |--------------------------------------------------------------------------
    | HELPERS MULTILENGUAJE
    |--------------------------------------------------------------------------
    */

    public function getTitle($locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->title[$locale]
            ?? $this->title['es']
            ?? '';
    }

    public function getSubtitle($locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->subtitle[$locale]
            ?? $this->subtitle['es']
            ?? '';
    }

    public function getButtonText($locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->button_text[$locale]
            ?? $this->button_text['es']
            ?? '';
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS — usados en hero.blade.php como $slide->media_url / media_type
    |--------------------------------------------------------------------------
    */

    /**
     * URL pública de la imagen del slide.
     * Blade usa: $slide->media_url
     */
    public function getMediaUrlAttribute(): string
    {
        return asset('storage/' . $this->image);
    }

    /**
     * Tipo de media — siempre 'image' (no hay soporte de video en este modelo).
     * Blade usa: $slide->media_type
     */
    public function getMediaTypeAttribute(): string
    {
        return 'image';
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
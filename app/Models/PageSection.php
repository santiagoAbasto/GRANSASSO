<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    protected $fillable = [
        'page_id',
        'type',
        'title',
        'subtitle',
        'content',
        'image',
        'button_text',
        'button_link',
        'order',
        'is_active'
    ];

    protected $casts = [
        'title' => 'array',
        'subtitle' => 'array',
        'content' => 'array',
        'button_text' => 'array',
        'is_active' => 'boolean'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function images()
    {
        return $this->hasMany(PageSectionImage::class, 'section_id');
    }

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

    public function getContent($locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->content[$locale]
            ?? $this->content['es']
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
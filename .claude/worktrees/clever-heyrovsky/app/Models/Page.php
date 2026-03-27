<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'meta_title',
        'meta_description',
        'is_active'
    ];

    protected $casts = [
        'title' => 'array',
        'meta_title' => 'array',
        'meta_description' => 'array',
        'is_active' => 'boolean'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function sections()
    {
        return $this->hasMany(PageSection::class);
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

    public function getMetaTitle($locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->meta_title[$locale]
            ?? $this->meta_title['es']
            ?? '';
    }

    public function getMetaDescription($locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->meta_description[$locale]
            ?? $this->meta_description['es']
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
}
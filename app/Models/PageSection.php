<?php

namespace App\Models;

use App\Traits\SanitizesHtml;
use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    use SanitizesHtml;

    protected $fillable = [
        'page_id',
        'type',
        'title',
        'subtitle',
        'content',
        'image',
        'button_text',
        'button_link',
        'autoplay_seconds',
        'order',
        'is_active'
    ];

    protected $casts = [
        'title' => 'array',
        'subtitle' => 'array',
        'content' => 'array',
        'button_text' => 'array',
        'autoplay_seconds' => 'integer',
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

        return $this->sanitizePlainText($this->title[$locale]
            ?? $this->title['es']
            ?? '');
    }

    public function getSubtitle($locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->sanitizePlainText($this->subtitle[$locale]
            ?? $this->subtitle['es']
            ?? '');
    }

    public function getContent($locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->sanitizeHtml($this->content[$locale]
            ?? $this->content['es']
            ?? '');
    }

    public function getButtonText($locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->sanitizePlainText($this->button_text[$locale]
            ?? $this->button_text['es']
            ?? '');
    }

    public function getSafeButtonLinkAttribute(): ?string
    {
        return $this->sanitizeUrl($this->button_link, allowRelative: true);
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

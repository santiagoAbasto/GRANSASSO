<?php

namespace App\Models;

use App\Traits\SanitizesHtml;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use SanitizesHtml;

    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'video_url',
        'video_file',
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

    public function getSafeButtonLinkAttribute(): ?string
    {
        return $this->sanitizeUrl($this->button_link, allowRelative: true);
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
        if ($this->video_file) {
            return asset('storage/' . $this->video_file);
        }

        return $this->image ? asset('storage/' . $this->image) : '';
    }

    /**
     * Tipo de media del slide.
     * Blade usa: $slide->media_type
     */
    public function getMediaTypeAttribute(): string
    {
        if ($this->video_file) {
            return 'video';
        }

        if ($this->youtube_embed_url) {
            return 'youtube';
        }

        return 'image';
    }

    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        $url = trim((string) $this->video_url);

        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        parse_str($parts['query'] ?? '', $query);

        $videoId = null;

        if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $videoId = trim($parts['path'] ?? '', '/');
        } elseif (str_contains($host, 'youtube.com')) {
            if (($query['v'] ?? null)) {
                $videoId = $query['v'];
            } elseif (preg_match('#^/(embed|shorts)/([a-zA-Z0-9_-]{11})#', $parts['path'] ?? '', $matches)) {
                $videoId = $matches[2];
            }
        }

        if (! $videoId || ! preg_match('/^[a-zA-Z0-9_-]{11}$/', $videoId)) {
            return null;
        }

        return 'https://www.youtube.com/embed/' . $videoId . '?autoplay=1&mute=1&loop=1&controls=0&rel=0&playlist=' . $videoId;
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

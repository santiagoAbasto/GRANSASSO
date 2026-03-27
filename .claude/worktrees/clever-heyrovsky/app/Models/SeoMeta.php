<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoMeta extends Model
{
    protected $fillable = [
        'page',
        'title',
        'description',
        'keywords',
        'og_image',
    ];

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR — og_image_url
    |--------------------------------------------------------------------------
    | El layout accede a $_seo->og_image_url, así que exponemos un accessor
    | que devuelve la URL pública del archivo.
    */
    public function getOgImageUrlAttribute(): ?string
    {
        return $this->og_image
            ? asset('storage/' . $this->og_image)
            : null;
    }
}

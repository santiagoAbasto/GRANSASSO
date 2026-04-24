<?php

namespace App\Models;

class PublicNovedad extends Novedad
{
    protected $table = 'public_novedades';

    public function galleryImages()
    {
        return $this->hasMany(PublicNovedadImage::class)->orderByRaw('COALESCE(`order`, "") asc')->orderBy('id');
    }
}

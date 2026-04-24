<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicNovedadImage extends Model
{
    protected $fillable = [
        'public_novedad_id',
        'image',
        'order',
    ];

    protected $appends = ['image_url'];

    public function novedad()
    {
        return $this->belongsTo(PublicNovedad::class, 'public_novedad_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}

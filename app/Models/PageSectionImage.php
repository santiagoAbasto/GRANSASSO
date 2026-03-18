<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSectionImage extends Model
{
    protected $fillable = [
        'section_id',
        'image',
        'order'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function section()
    {
        return $this->belongsTo(PageSection::class);
    }
}
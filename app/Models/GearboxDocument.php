<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GearboxDocument extends Model
{
    protected $fillable = [
        'gearbox_id',
        'file',
        'type',
        'is_main'
    ];

    protected $casts = [
        'is_main' => 'boolean'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function gearbox()
    {
        return $this->belongsTo(Gearbox::class);
    }
}
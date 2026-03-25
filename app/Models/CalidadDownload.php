<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalidadDownload extends Model
{
    protected $fillable = [
        'name',
        'logo',
        'file',
        'file_format',
        'file_size',
        'order',
        'is_active',
    ];

    protected $casts = [
        'name'      => 'array',
        'is_active' => 'boolean',
    ];
}

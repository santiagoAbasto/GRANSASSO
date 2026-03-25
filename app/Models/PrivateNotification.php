<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivateNotification extends Model
{
    protected $table = 'private_notifications';

    protected $fillable = [
        'title',
        'body',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

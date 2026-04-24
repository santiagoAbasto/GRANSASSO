<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteVisitEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'ip_address',
        'user_agent',
        'path',
        'route_name',
        'locale',
        'private_user_id',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    public function privateUser()
    {
        return $this->belongsTo(PrivateUser::class, 'private_user_id');
    }
}

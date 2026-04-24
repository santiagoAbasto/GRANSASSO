<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivateUserLoginEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'private_user_id',
        'ip_address',
        'user_agent',
        'logged_in_at',
    ];

    protected $casts = [
        'logged_in_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(PrivateUser::class, 'private_user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class PrivateUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'private_users';

    protected $fillable = [
        'name',
        'lastname',
        'email',
        'password',
        'status',
        'discount_pct',
        'discount_extra_pct',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function paymentSubmissions()
    {
        return $this->hasMany(PaymentSubmission::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->name . ' ' . $this->lastname;
    }
}

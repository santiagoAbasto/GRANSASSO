<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'company_name',
        'address',
        'phone_1',
        'phone_2',
        'phone_3',
        'email_sales',
        'email_general',
        'facebook',
        'instagram',
        'linkedin'
    ];

    protected $casts = [
        'address' => 'array'
    ];
}

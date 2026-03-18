<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleModel extends Model
{
    protected $table = 'models';

    protected $fillable = [
        'brand_id',
        'name',
        'is_active'
    ];

    protected $casts = [
        'name' => 'array',
        'is_active' => 'boolean'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'model_id');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

    public function getName($locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->name[$locale]
            ?? $this->name['es']
            ?? '';
    }
}
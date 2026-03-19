<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'logo',
        'order',
        'is_active',
    ];

    protected $casts = [
        'name'      => 'array',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function models()
    {
        return $this->hasMany(Model::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS MULTILENGUAJE
    |--------------------------------------------------------------------------
    */

    public function getName($locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->name[$locale]
            ?? $this->name['es']
            ?? '';
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
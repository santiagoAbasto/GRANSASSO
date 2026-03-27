<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'logo',
        'order',
        'is_active',
        'show_in_home',
    ];

    protected $casts = [
        'name'         => 'array',
        'is_active'    => 'boolean',
        'show_in_home' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function gearboxes(): HasMany
    {
        return $this->hasMany(Gearbox::class);
    }

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
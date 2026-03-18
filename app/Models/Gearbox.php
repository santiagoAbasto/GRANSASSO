<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Gearbox extends Model
{
    protected $fillable = [
        'brand_id',
        'name',
        'slug',
        'compatible_vehicles',
        'description',
        'image',
        'is_active',
    ];

    protected $casts = [
        'description' => 'array',  // {"es":"...","en":"...","pt":"..."}
        'is_active'   => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | BOOT
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($gearbox) {
            if (!$gearbox->slug) {
                $gearbox->slug = Str::slug($gearbox->name);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function documents()
    {
        return $this->hasMany(GearboxDocument::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS MULTILENGUAJE
    |--------------------------------------------------------------------------
    */

    public function getDescription($locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        return $this->description[$locale]
            ?? $this->description['es']
            ?? '';
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function getMainDocument()
    {
        return $this->documents()
            ->where('is_main', true)
            ->first();
    }

    /**
     * Devuelve las variantes disponibles para esta caja
     * (derivadas de los productos ya cargados).
     * Usado para generar los headers de la ficha técnica.
     */
    public function getVariants(): array
    {
        return $this->products()
            ->with('versions')
            ->get()
            ->flatMap(fn ($p) => $p->versions->pluck('version'))
            ->unique()
            ->values()
            ->all();
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
}

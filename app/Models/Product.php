<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'gearbox_id',
        'figure',
        'cod_gs',
        'code_oem',
        'slug',
        'nombre',
        'referencia_original',
        'referencias_adicionales',
        'description',
        'image',
        // Campos extra: datos que NO vienen del catálogo Excel
        'nota_comercial',  // Visible al cliente: precios, promo, disponibilidad especial
        'nota_interna',    // Nota privada para equipo de ventas / admin
        'is_active',
    ];

    protected $casts = [
        'nombre'                  => 'array',  // {"es":"...","en":"...","pt":"..."}
        'description'             => 'array',  // {"es":"...","en":"...","pt":"..."}
        'referencias_adicionales' => 'array',  // ["A6137W","TT-A-006137"]
        'is_active'               => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function gearbox()
    {
        return $this->belongsTo(Gearbox::class);
    }

    public function applications()
    {
        return $this->belongsToMany(Application::class, 'product_applications');
    }

    public function versions()
    {
        return $this->hasMany(ProductVersion::class);
    }

    public function documents()
    {
        return $this->hasMany(ProductDocument::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS MULTILENGUAJE
    |--------------------------------------------------------------------------
    */

    public function getNombre($locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        return $this->nombre[$locale]
            ?? $this->nombre['es']
            ?? '';
    }

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

    public function hasVersion($version): bool
    {
        return $this->versions()
            ->where('version', $version)
            ->where('is_available', true)
            ->exists();
    }

    public function getTechnicalSheet()
    {
        // 1. Documento propio
        $doc = $this->documents()
            ->where('is_main', true)
            ->first();

        if ($doc) return $doc;

        // 2. Documento de la caja
        $doc = $this->gearbox?->getMainDocument();

        if ($doc) return $doc;

        // 3. null → generar automático
        return null;
    }
}

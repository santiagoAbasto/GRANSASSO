<?php

namespace App\Models;

use App\Support\CatalogImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Product — producto base del catálogo.
 *
 * Un producto está definido de forma ÚNICA por:
 *   gearbox_id + figure + code_gs
 *
 * Cada producto tiene MÚLTIPLES ProductItems (uno por fila del Excel).
 * Cada ProductItem representa un código OEM con sus datos de compatibilidad.
 *
 * @property int         $id
 * @property int|null    $gearbox_id
 * @property string|null $figure       Número de figura del catálogo
 * @property string|null $code_gs      Código interno Gran Sasso
 * @property array|null  $name         JSON multilingüe {"es":"...","en":"...","pt":"..."}
 * @property array|null  $description  JSON multilingüe
 * @property string|null $image        Imagen principal (storage path)
 * @property string      $slug         URL-friendly identifier
 * @property bool        $is_active
 */
class Product extends Model
{
    protected $fillable = [
        'gearbox_id',
        'figure',
        'code_gs',
        'name',
        'description',
        'image',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'name'        => 'array',  // {"es":"...","en":"...","pt":"..."}
        'description' => 'array',  // {"es":"...","en":"...","pt":"..."}
        'is_active'   => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function gearbox(): BelongsTo
    {
        return $this->belongsTo(Gearbox::class);
    }

    /**
     * Cajas adicionales donde este producto también debe mostrarse.
     * La caja principal sigue viviendo en products.gearbox_id para no
     * romper la lógica del importador y del catálogo base.
     */
    public function secondaryGearboxes(): BelongsToMany
    {
        return $this->belongsToMany(Gearbox::class, 'product_gearbox_links')
            ->withTimestamps()
            ->orderBy('name');
    }

    /**
     * Items del producto (uno por fila del Excel).
     * Aquí viven: oem_code, application, extra_data (G60, G85, etc.)
     */
    public function items(): HasMany
    {
        return $this->hasMany(ProductItem::class);
    }

    /**
     * Documentos técnicos propios del producto (fichas, manuales).
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ProductDocument::class);
    }

    /**
     * Galería de imágenes del producto.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Imagen principal del producto.
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS — Visibilidad por caja
    |--------------------------------------------------------------------------
    */

    public function isVisibleInGearbox(int $gearboxId): bool
    {
        if ((int) $this->gearbox_id === $gearboxId) {
            return true;
        }

        if ($this->relationLoaded('secondaryGearboxes')) {
            return $this->secondaryGearboxes->contains(fn (Gearbox $gearbox) => (int) $gearbox->id === $gearboxId);
        }

        return $this->secondaryGearboxes()->where('gearboxes.id', $gearboxId)->exists();
    }

    public function scopeVisibleInGearbox(Builder $query, int $gearboxId): Builder
    {
        return $query->where(function (Builder $q) use ($gearboxId) {
            $q->where('gearbox_id', $gearboxId)
                ->orWhereHas('secondaryGearboxes', fn (Builder $secondary) => $secondary->where('gearboxes.id', $gearboxId));
        });
    }

    public function scopeVisibleInBrand(Builder $query, int $brandId): Builder
    {
        return $query->where(function (Builder $q) use ($brandId) {
            $q->whereHas('gearbox', fn (Builder $primary) => $primary->where('brand_id', $brandId))
                ->orWhereHas('secondaryGearboxes', fn (Builder $secondary) => $secondary->where('gearboxes.brand_id', $brandId));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS — Multilenguaje
    |--------------------------------------------------------------------------
    */

    /**
     * Nombre del producto en el idioma actual (o fallback a ES).
     */
    public function getName(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $name   = $this->name;

        if (! $name) {
            return null;
        }

        return $name[$locale] ?? $name['es'] ?? null;
    }

    /**
     * Alias de getName() — mantiene compatibilidad con vistas que usan getNombre().
     */
    public function getNombre(?string $locale = null): string
    {
        return $this->getName($locale) ?? '';
    }

    /**
     * Descripción del producto en el idioma actual (o fallback a ES).
     */
    public function getDescription(?string $locale = null): ?string
    {
        $locale      = $locale ?? app()->getLocale();
        $description = $this->description;

        if (! $description) {
            return null;
        }

        return $description[$locale] ?? $description['es'] ?? null;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS — Accesores de compatibilidad con vistas anteriores
    |
    | Las vistas actualmente referencian $product->code_oem,
    | $product->referencia_original, etc.
    | Estos accesores los computan dinámicamente desde items()
    | para que las vistas existentes no rompan durante la transición.
    |--------------------------------------------------------------------------
    */

    /**
     * Primer código OEM del producto (del primer item).
     * Acceso: $product->code_oem
     */
    public function getCodeOemAttribute(): ?string
    {
        // Usa la relación si ya fue cargada (eager loading), sino query puntual
        if ($this->relationLoaded('items')) {
            return $this->items->first()?->oem_code;
        }

        return $this->items()->value('oem_code');
    }

    /**
     * Referencia original del primer item.
     * Acceso: $product->referencia_original
     */
    public function getReferenciaOriginalAttribute(): ?string
    {
        if ($this->relationLoaded('items')) {
            $item = $this->items->first();
        } else {
            $item = $this->items()->first();
        }

        return $item?->getReferenciaOriginal();
    }

    /**
     * Referencias adicionales unificadas de todos los items.
     * Acceso: $product->referencias_adicionales
     *
     * @return string[]
     */
    public function getReferenciasAdicionalesAttribute(): array
    {
        $items = $this->relationLoaded('items')
            ? $this->items
            : $this->items()->get();

        return $items
            ->flatMap(fn ($item) => $item->getReferenciasAdicionales())
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    public function getResolvedImageUrlAttribute(): ?string
    {
        $primaryImageUrl = null;

        if ($this->relationLoaded('primaryImage')) {
            $primaryImageUrl = $this->primaryImage?->url;
        } else {
            $primaryPath = $this->primaryImage()->value('path');
            $primaryImageUrl = CatalogImage::urlForPath($primaryPath);
        }

        return $primaryImageUrl
            ?? CatalogImage::urlForPath($this->image)
            ?? CatalogImage::placeholderUrl();
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS — Variantes / Compatibilidad
    |--------------------------------------------------------------------------
    */

    /**
     * Devuelve todas las variantes/aplicaciones disponibles para este producto,
     * deduplicadas de todos sus items.
     *
     * Con la nueva arquitectura, las variantes se leen del campo `application`
     * de cada ProductItem (ej: "G60", "G85"), no de extra_data.
     *
     * @return string[]
     */
    public function getAvailableVariants(): array
    {
        $items = $this->relationLoaded('items')
            ? $this->items
            : $this->items()->get();

        return $items
            ->pluck('application')
            ->filter()               // quita nulls (items genéricos sin variante)
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * ¿Algún item de este producto es compatible con la variante dada?
     */
    public function hasVariant(string $variant): bool
    {
        return in_array($variant, $this->getAvailableVariants(), true);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS — Documentación
    |--------------------------------------------------------------------------
    */

    /**
     * Ficha técnica: primero busca en el propio producto,
     * luego en la caja de cambios.
     */
    public function getTechnicalSheet()
    {
        $doc = $this->documents()->where('is_main', true)->first();

        if ($doc) {
            return $doc;
        }

        return $this->gearbox?->getMainDocument();
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

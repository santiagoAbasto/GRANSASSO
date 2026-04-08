<?php

namespace App\Models;

use App\Support\CatalogImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Gearbox — caja de cambios.
 *
 * Representa el segundo nivel de la jerarquía del catálogo:
 *   Brand → Gearbox → Product → ProductItem
 *
 * extra_field_1 y extra_field_2 son campos manuales (no del Excel)
 * que aplican a TODOS los productos de la caja.
 * Se editan masivamente desde el admin (una sola edición afecta
 * a todos los productos de la caja).
 *
 * @property int         $id
 * @property int         $brand_id
 * @property string      $name
 * @property string      $slug
 * @property array|null  $description       JSON multilingüe
 * @property string|null $image
 * @property string|null $compatible_vehicles
 * @property array|null  $catalog_meta      Metadata cruda del catálogo importado
 * @property string      $order
 * @property bool        $is_active
 * @property string|null $extra_field_1     Campo manual — se edita por caja
 * @property string|null $extra_field_2     Campo manual — se edita por caja
 */
class Gearbox extends Model
{
    protected $fillable = [
        'brand_id',
        'name',
        'slug',
        'compatible_vehicles',
        'catalog_meta',
        'description',
        'image',
        'is_active',
        'order',
        'extra_field_1',   // ← Campo manual (no viene del Excel)
        'extra_field_2',   // ← Campo manual (no viene del Excel)
    ];

    protected $casts = [
        'description' => 'array',
        'catalog_meta' => 'array',
        'is_active'   => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Gearbox $gearbox) {
            if (! $gearbox->slug) {
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
    | HELPERS — Multilenguaje
    |--------------------------------------------------------------------------
    */

    public function getDescription(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        return $this->description[$locale]
            ?? $this->description['es']
            ?? '';
    }

    public function getResolvedImageUrlAttribute(): ?string
    {
        return CatalogImage::urlForPath($this->image)
            ?? $this->brand?->logo_url
            ?? CatalogImage::placeholderUrl();
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS — Documentación
    |--------------------------------------------------------------------------
    */

    public function getMainDocument()
    {
        return $this->documents()->where('is_main', true)->first();
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS — Variantes
    |
    | Agrega todas las variantes disponibles de los items de los productos
    | de esta caja. Útil para mostrar en la ficha de la caja qué variantes
    | de transmisión soporta.
    |--------------------------------------------------------------------------
    */

    /**
     * Todas las variantes disponibles en los productos de esta caja.
     * (deduplica across todos los product_items)
     *
     * @return string[]
     */
    public function getVariants(): array
    {
        return ProductItem::whereHas('product', fn ($q) => $q->where('gearbox_id', $this->id))
            ->get()
            ->flatMap(fn ($item) => $item->getCompatibleVariants())
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

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}

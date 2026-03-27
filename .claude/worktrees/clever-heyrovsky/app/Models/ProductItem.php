<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductItem — item individual de un producto del catálogo.
 *
 * ══ ARQUITECTURA ═══════════════════════════════════════════════════════════
 *
 *  Product  →  hasMany  →  ProductItem
 *
 *  Un producto (identificado por code_gs) puede tener múltiples items:
 *    · Un item por variante de caja compatible (G60, G85, G3/36, etc.)
 *    · Un item por código OEM alternativo
 *
 * ══ EJEMPLO ════════════════════════════════════════════════════════════════
 *
 *  Product: code_gs=826, name="SEPARADOR"
 *  ├── ProductItem: oem_code="976-262-0462", application="G60", figure="116"
 *  └── ProductItem: oem_code="976-262-0462", application="G85", figure="116"
 *
 * ══ CAMPO application ══════════════════════════════════════════════════════
 *
 *  Contiene el nombre de la variante de caja compatible: "G60", "G85", etc.
 *  Si un producto no tiene variante definida → application = null.
 *
 *  application_type:
 *    'generic'  → aplica a toda la caja (sin variante específica)
 *    'specific' → aplica a la variante indicada en application
 *
 * ══ extra_data ══════════════════════════════════════════════════════════════
 *
 *  JSON para datos adicionales del item (referencias, notas extendidas, etc.)
 *  No se usa para variantes (eso lo maneja el campo application).
 *
 * @property int         $id
 * @property int         $product_id
 * @property string|null $figure          Número de figura del catálogo
 * @property string|null $oem_code        Código OEM del fabricante original
 * @property string|null $application     Variante de caja: "G60", "G85", etc.
 * @property string      $application_type 'generic' | 'specific'
 * @property string|null $notes
 * @property array|null  $extra_data       JSON: referencia_original, referencias_adicionales, etc.
 */
class ProductItem extends Model
{
    protected $fillable = [
        'product_id',
        'figure',
        'oem_code',
        'application',
        'application_type',
        'notes',
        'extra_data',
    ];

    protected $casts = [
        // extra_data almacena:
        //   "referencia_original": "A-00613-7W"
        //   "referencias_adicionales": ["TT-A-006137", "A6137W"]
        //   (otros datos libres que no tienen columna propia)
        'extra_data' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS — Aplicación / variante
    |--------------------------------------------------------------------------
    */

    /**
     * ¿Este item aplica a todos los vehículos/variantes de la caja?
     */
    public function isGeneric(): bool
    {
        return $this->application_type === 'generic';
    }

    /**
     * ¿Este item está ligado a una variante específica (G60, G85, etc.)?
     */
    public function isSpecific(): bool
    {
        return $this->application_type === 'specific';
    }

    /**
     * Devuelve las variantes de caja compatibles para este item.
     *
     * Con la nueva arquitectura, cada item tiene UNA aplicación/variante.
     * Si application = "G60" → devuelve ["G60"].
     * Si application = null  → devuelve [] (genérico, sin variante).
     *
     * @return string[]
     */
    public function getCompatibleVariants(): array
    {
        return $this->application ? [$this->application] : [];
    }

    /**
     * ¿Este item es compatible con la variante dada? (ej: 'G60', 'G85')
     * Comparación case-insensitive.
     */
    public function hasCompatibility(string $variant): bool
    {
        return $this->application !== null
            && strtolower($this->application) === strtolower($variant);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS — Referencias (en extra_data)
    |--------------------------------------------------------------------------
    */

    /**
     * Referencia original del fabricante (almacenada en extra_data).
     */
    public function getReferenciaOriginal(): ?string
    {
        return $this->extra_data['referencia_original'] ?? null;
    }

    /**
     * Referencias adicionales (almacenadas en extra_data).
     *
     * @return string[]
     */
    public function getReferenciasAdicionales(): array
    {
        return $this->extra_data['referencias_adicionales'] ?? [];
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeGeneric($query)
    {
        return $query->where('application_type', 'generic');
    }

    public function scopeSpecific($query)
    {
        return $query->where('application_type', 'specific');
    }

    /**
     * Items compatibles con la variante dada.
     * Ej: ProductItem::forVariant('G60') → items donde application = 'G60'
     */
    public function scopeForVariant($query, string $variant)
    {
        return $query->where('application', $variant);
    }
}

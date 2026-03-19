<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Gearbox extends Model
{
    protected $fillable = [
        'brand_id', 'name', 'slug', 'compatible_vehicles',
        'description', 'image', 'is_active', 'order',
    ];

    protected $casts = [
        'description' => 'array',
        'is_active'   => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($gearbox) {
            if (!$gearbox->slug) {
                $gearbox->slug = Str::slug($gearbox->name);
            }
        });
    }

    public function brand()      { return $this->belongsTo(Brand::class); }
    public function products()   { return $this->hasMany(Product::class); }
    public function documents()  { return $this->hasMany(GearboxDocument::class); }
    public function applications(){ return $this->hasMany(Application::class); }

    public function getDescription($locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->description[$locale] ?? $this->description['es'] ?? '';
    }

    public function getMainDocument()
    {
        return $this->documents()->where('is_main', true)->first();
    }

    public function getVariants(): array
    {
        return $this->products()->with('versions')->get()
            ->flatMap(fn ($p) => $p->versions->pluck('version'))
            ->unique()->values()->all();
    }

    public function scopeActive($query)   { return $query->where('is_active', true); }
    public function scopeOrdered($query)  { return $query->orderBy('order'); }
}

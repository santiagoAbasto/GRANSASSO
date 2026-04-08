<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class CatalogImage
{
    public const PLACEHOLDER_PATH = 'images/placeholders/catalog-placeholder.png';

    public static function urlForPath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk('public')->exists($path)
            ? asset('storage/' . ltrim($path, '/'))
            : null;
    }

    public static function placeholderUrl(): ?string
    {
        return self::urlForPath(self::PLACEHOLDER_PATH);
    }

    public static function storagePathForPath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $normalized = ltrim($path, '/');

        return Storage::disk('public')->exists($normalized)
            ? Storage::disk('public')->path($normalized)
            : null;
    }

    public static function placeholderStoragePath(): ?string
    {
        return self::storagePathForPath(self::PLACEHOLDER_PATH);
    }
}

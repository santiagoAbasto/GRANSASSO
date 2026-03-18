<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remove brands that don't belong to Gran Sasso's catalog.
     * These were imported from the previous project.
     */
    public function up(): void
    {
        $remove = ['dodge', 'agrale', 'general-motors', 'fiat'];

        DB::table('brands')
            ->whereIn('slug', $remove)
            ->delete();
    }

    public function down(): void
    {
        // No se restauran — re-run the BrandSeeder si es necesario
    }
};

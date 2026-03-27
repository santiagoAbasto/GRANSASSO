<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Corrige el tipo de la columna `figure` en products.
 *
 * Quedó como JSON por un rename histórico (name → figure en una
 * migración anterior donde 'name' era json multilingüe).
 * figure debe ser varchar simple (ej: "15", "37", "G60").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // En MySQL ALTER COLUMN cambia el tipo sin perder datos NULL
            $table->string('figure', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('figure')->nullable()->change();
        });
    }
};

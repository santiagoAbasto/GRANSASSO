<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega `figure` a product_items.
 *
 * Con la nueva arquitectura, la figura del catálogo vive en el ITEM
 * (no solo en el producto base) porque diferentes OEM dentro del mismo
 * producto pueden tener figuras distintas.
 *
 * Se mantiene también en products para compatibilidad con vistas web.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_items', function (Blueprint $table) {
            if (! Schema::hasColumn('product_items', 'figure')) {
                $table->string('figure', 100)->nullable()->after('product_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_items', function (Blueprint $table) {
            if (Schema::hasColumn('product_items', 'figure')) {
                $table->dropColumn('figure');
            }
        });
    }
};

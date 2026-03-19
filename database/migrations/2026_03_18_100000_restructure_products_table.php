<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Reemplazar model_id por gearbox_id
            if (Schema::hasColumn('products', 'model_id')) {
                $table->dropForeign(['model_id']);
                $table->dropColumn('model_id');
            }

            if (!Schema::hasColumn('products', 'gearbox_id')) {
                $table->foreignId('gearbox_id')->nullable()->constrained()->nullOnDelete()->after('id');
            }

            // Renombrar 'name' → no aplica, reemplazar por figure + cod_gs + code_oem
            if (Schema::hasColumn('products', 'name') && !Schema::hasColumn('products', 'figure')) {
                $table->renameColumn('name', 'figure');
            }

            if (Schema::hasColumn('products', 'code') && !Schema::hasColumn('products', 'code_oem')) {
                $table->renameColumn('code', 'code_oem');
            }

            // Campos nuevos
            if (!Schema::hasColumn('products', 'cod_gs')) {
                $table->string('cod_gs', 50)->nullable()->after('gearbox_id');
            }

            if (!Schema::hasColumn('products', 'slug')) {
                $table->string('slug')->nullable()->after('cod_gs');
            }

            // Campos extra: datos que NO vienen de catálogo
            if (!Schema::hasColumn('products', 'nota_comercial')) {
                $table->text('nota_comercial')->nullable()->comment('Info visible al cliente: precios, promo, disponibilidad')->after('referencias_adicionales');
            }

            if (!Schema::hasColumn('products', 'nota_interna')) {
                $table->text('nota_interna')->nullable()->comment('Nota interna para el equipo de ventas/admin')->after('nota_comercial');
            }

            // extra_1 / extra_2 legacy (si existían)
            if (Schema::hasColumn('products', 'extra_1') && !Schema::hasColumn('products', 'nota_comercial')) {
                $table->renameColumn('extra_1', 'nota_comercial');
            }

            if (Schema::hasColumn('products', 'extra_2') && !Schema::hasColumn('products', 'nota_interna')) {
                $table->renameColumn('extra_2', 'nota_interna');
            }
        });

        // Generar slugs únicos para productos existentes
        $products = DB::table('products')->whereNull('slug')->orWhere('slug', '')->get();
        foreach ($products as $product) {
            $base = $product->code_oem ?? ($product->cod_gs ?? 'producto-' . $product->id);
            $slug = \Illuminate\Support\Str::slug($base);

            // Garantizar unicidad
            $count = DB::table('products')
                ->where('slug', $slug)
                ->where('id', '!=', $product->id)
                ->count();

            if ($count > 0) {
                $slug = $slug . '-' . $product->id;
            }

            DB::table('products')->where('id', $product->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['gearbox_id', 'slug', 'nota_comercial', 'nota_interna', 'cod_gs']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega extra_field_1 y extra_field_2 a gearboxes.
 *
 * ¿Por qué en gearboxes y NO en products/product_items?
 *
 * El cliente necesita 2 campos que:
 *   - NO vienen del Excel (se cargan manualmente)
 *   - Se editan MASIVAMENTE por caja de cambios
 *   - Aplican a TODOS los productos de esa caja
 *
 * Ponerlos en gearboxes elimina duplicación (el mismo valor
 * se almacena una sola vez en lugar de repetirse en cada producto).
 *
 * Ejemplo de uso:
 *   extra_field_1 = "Precio referencia lista"
 *   extra_field_2 = "Disponibilidad stock"
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gearboxes', function (Blueprint $table) {
            if (! Schema::hasColumn('gearboxes', 'extra_field_1')) {
                $table->text('extra_field_1')->nullable()->after('is_active');
            }
            if (! Schema::hasColumn('gearboxes', 'extra_field_2')) {
                $table->text('extra_field_2')->nullable()->after('extra_field_1');
            }
        });
    }

    public function down(): void
    {
        Schema::table('gearboxes', function (Blueprint $table) {
            $table->dropColumn(['extra_field_1', 'extra_field_2']);
        });
    }
};

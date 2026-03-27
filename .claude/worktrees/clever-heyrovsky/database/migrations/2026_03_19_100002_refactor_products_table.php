<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Refactoriza la tabla products al nuevo modelo:
 *
 * ANTES (arquitectura errónea — 1 fila Excel = 1 product):
 *   cod_gs, nombre, code_oem, referencia_original,
 *   referencias_adicionales, nota_comercial, nota_interna
 *
 * DESPUÉS (arquitectura correcta — 1 product = N product_items):
 *   code_gs   (renombrado de cod_gs)
 *   name      (renombrado de nombre)
 *   — los campos de detalle viven en product_items —
 *
 * CAMPOS QUE SE MUEVEN A product_items:
 *   code_oem              → product_items.oem_code
 *   referencia_original   → product_items.extra_data['referencia_original']
 *   referencias_adicionales → product_items.extra_data['referencias_adicionales']
 *   nota_comercial        → product_items.notes  (o campo manual)
 *   nota_interna          → eliminado (gearbox.extra_field_1 / extra_field_2 reemplazan la lógica)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── PASO 1: Renombrar columnas ──────────────────────────────────────────
        // cod_gs  → code_gs   (convención snake_case consistente)
        // nombre  → name      (campo multilingüe JSON)
        if (Schema::hasColumn('products', 'cod_gs')) {
            Schema::table('products', function (Blueprint $table) {
                $table->renameColumn('cod_gs', 'code_gs');
            });
        }

        if (Schema::hasColumn('products', 'nombre')) {
            Schema::table('products', function (Blueprint $table) {
                $table->renameColumn('nombre', 'name');
            });
        }

        // ── PASO 2: Eliminar columnas que se mueven a product_items ─────────────
        Schema::table('products', function (Blueprint $table) {
            $toDrop = [];

            foreach (['code_oem', 'referencia_original', 'referencias_adicionales',
                      'nota_comercial', 'nota_interna'] as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $toDrop[] = $col;
                }
            }

            if (! empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });

        // ── PASO 3: Índice único (gearbox_id, code_gs) ─────────────────────────
        // Evita importar dos veces el mismo producto en la misma caja.
        // En MySQL, dos NULLs en una columna unique NO colisionan (NULL ≠ NULL),
        // por eso productos sin code_gs se diferencian por figure en PHP.
        Schema::table('products', function (Blueprint $table) {
            // Solo crear si no existe
            try {
                $table->unique(['gearbox_id', 'code_gs'], 'products_gearbox_code_gs_unique');
            } catch (\Exception $e) {
                // Ya existe — ignorar
            }
        });
    }

    public function down(): void
    {
        // Restaurar columnas eliminadas
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'code_oem')) {
                $table->string('code_oem')->nullable()->after('figure');
            }
            if (! Schema::hasColumn('products', 'referencia_original')) {
                $table->string('referencia_original')->nullable();
            }
            if (! Schema::hasColumn('products', 'referencias_adicionales')) {
                $table->json('referencias_adicionales')->nullable();
            }
            if (! Schema::hasColumn('products', 'nota_comercial')) {
                $table->text('nota_comercial')->nullable();
            }
            if (! Schema::hasColumn('products', 'nota_interna')) {
                $table->text('nota_interna')->nullable();
            }
        });

        // Revertir renombres
        if (Schema::hasColumn('products', 'code_gs')) {
            Schema::table('products', function (Blueprint $table) {
                $table->renameColumn('code_gs', 'cod_gs');
            });
        }
        if (Schema::hasColumn('products', 'name')) {
            Schema::table('products', function (Blueprint $table) {
                $table->renameColumn('name', 'nombre');
            });
        }

        // Eliminar índice
        Schema::table('products', function (Blueprint $table) {
            try {
                $table->dropUnique('products_gearbox_code_gs_unique');
            } catch (\Exception $e) {}
        });
    }
};

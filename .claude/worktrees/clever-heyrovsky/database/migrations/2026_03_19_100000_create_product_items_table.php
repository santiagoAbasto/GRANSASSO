<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * product_items — cada fila del Excel genera un item.
 *
 * Un Product (definido por fig + code_gs) tiene MUCHOS ProductItems.
 * Cada item representa un código OEM con su aplicación y datos dinámicos.
 *
 * extra_data (JSON) almacena:
 *   - columnas dinámicas del Excel: G60, G85, G3/36, etc. → bool
 *   - referencia_original                                  → string|null
 *   - referencias_adicionales                              → array|null
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Código OEM de esta fila del Excel (nullable: puede faltar en alguna fila)
            $table->string('oem_code', 100)->nullable();

            // Vehículos / modelos compatibles
            // null   → producto genérico para toda la caja (application_type = generic)
            // string → modelos específicos  (application_type = specific)
            $table->text('application')->nullable();

            // 'generic' | 'specific'
            // Se determina automáticamente en el importador según si application es null
            $table->string('application_type', 20)->default('generic');

            // Notas libres (columna "notas" del Excel si existe, o campo manual)
            $table->text('notes')->nullable();

            // Datos dinámicos del Excel + referencias
            // Estructura ejemplo:
            // {
            //   "G60": true,
            //   "G85": false,
            //   "referencia_original": "A-00613-7W",
            //   "referencias_adicionales": ["TT-A-006137", "A6137W"]
            // }
            $table->json('extra_data')->nullable();

            $table->timestamps();

            // Un producto puede tener múltiples oem_codes (o null)
            // Índice compuesto para búsquedas eficientes
            $table->index(['product_id', 'oem_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_items');
    }
};

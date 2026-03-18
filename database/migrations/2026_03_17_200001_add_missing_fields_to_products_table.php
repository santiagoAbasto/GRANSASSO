<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Título corto visible en cards del listado — multilingüe (es/en/pt)
            // Ejemplo: {"es": "Anillo Sincro 3ra/4ta", "en": "Sync Ring 3rd/4th", "pt": "..."}
            $table->json('nombre')->nullable()->after('code');

            // Referencia original del fabricante (código técnico, no traduce)
            // Ejemplo: "A-006137W"
            $table->string('referencia_original')->nullable()->after('nombre');

            // Referencias adicionales — array JSON de códigos (no traducen)
            // Ejemplo: ["A6137W", "TT-A-006137", "A-6137.W"]
            $table->json('referencias_adicionales')->nullable()->after('referencia_original');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['nombre', 'referencia_original', 'referencias_adicionales']);
        });
    }
};

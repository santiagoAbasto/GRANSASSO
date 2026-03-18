<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gearboxes', function (Blueprint $table) {
            // Modelos de vehículo compatibles con esta caja de cambios
            // Tal como aparece en el catálogo Excel
            // Ejemplo: "L1218/1518/1215C/1315 L1620ELEC./1622/O500 ATEGO1718"
            $table->text('compatible_vehicles')->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('gearboxes', function (Blueprint $table) {
            $table->dropColumn('compatible_vehicles');
        });
    }
};

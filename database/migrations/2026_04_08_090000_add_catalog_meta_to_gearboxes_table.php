<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gearboxes', function (Blueprint $table) {
            $table->json('catalog_meta')->nullable()->after('compatible_vehicles');
        });
    }

    public function down(): void
    {
        Schema::table('gearboxes', function (Blueprint $table) {
            $table->dropColumn('catalog_meta');
        });
    }
};

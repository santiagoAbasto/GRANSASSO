<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gearboxes', function (Blueprint $table) {
            $table->dropUnique('gearboxes_slug_unique');
            $table->unique(['brand_id', 'slug'], 'gearboxes_brand_id_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('gearboxes', function (Blueprint $table) {
            $table->dropUnique('gearboxes_brand_id_slug_unique');
            $table->unique('slug', 'gearboxes_slug_unique');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Brands: drop compatible_with, order integer → varchar
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn('compatible_with');
        });
        DB::statement('ALTER TABLE brands MODIFY COLUMN `order` VARCHAR(20) NOT NULL DEFAULT "A"');

        // 2. Sliders: order integer → varchar
        DB::statement('ALTER TABLE sliders MODIFY COLUMN `order` VARCHAR(20) NOT NULL DEFAULT "A"');

        // 3. Page sections: order integer → varchar
        DB::statement('ALTER TABLE page_sections MODIFY COLUMN `order` VARCHAR(20) NOT NULL DEFAULT "A"');

        // 4. Gearboxes: add order varchar
        Schema::table('gearboxes', function (Blueprint $table) {
            $table->string('order', 20)->default('A')->after('is_active');
        });
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE brands MODIFY COLUMN `order` INT NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE sliders MODIFY COLUMN `order` INT NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE page_sections MODIFY COLUMN `order` INT NOT NULL DEFAULT 0');

        Schema::table('brands', function (Blueprint $table) {
            $table->json('compatible_with')->nullable()->after('name');
        });
        Schema::table('gearboxes', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};

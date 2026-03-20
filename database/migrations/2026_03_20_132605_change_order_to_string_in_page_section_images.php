<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_section_images', function (Blueprint $table) {
            $table->string('order', 20)->default('A')->change();
        });
    }

    public function down(): void
    {
        Schema::table('page_section_images', function (Blueprint $table) {
            $table->integer('order')->default(0)->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_metas', function (Blueprint $table) {
            $table->id();
            $table->string('page')->unique(); // 'home', 'products', 'novedades', etc.
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('keywords')->nullable();
            $table->string('og_image')->nullable(); // ruta relativa en storage
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_metas');
    }
};

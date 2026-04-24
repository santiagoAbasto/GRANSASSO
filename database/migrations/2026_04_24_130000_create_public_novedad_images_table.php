<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_novedad_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('public_novedad_id')->constrained('public_novedades')->cascadeOnDelete();
            $table->string('image');
            $table->string('order', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_novedad_images');
    }
};

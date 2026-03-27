<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('application_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // 🔥 evita duplicados (CLAVE)
            $table->unique(['product_id', 'application_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_applications');
    }
};
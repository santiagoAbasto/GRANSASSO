<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gearbox_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('gearbox_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('file');

            // 🔥 tipo controlado
            $table->enum('type', ['ficha', 'manual', 'catalogo'])->default('ficha');

            $table->boolean('is_main')->default(false);

            $table->timestamps();

            // 🔥 índices
            $table->index(['gearbox_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gearbox_documents');
    }
};
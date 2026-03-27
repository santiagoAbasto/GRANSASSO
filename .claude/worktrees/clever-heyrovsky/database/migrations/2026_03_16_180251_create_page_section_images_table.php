<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_section_images', function (Blueprint $table) {

            $table->id();

            $table->foreignId('section_id')
                ->constrained('page_sections')
                ->cascadeOnDelete();

            $table->string('image');

            $table->integer('order')->default(0);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_section_images');
    }
};
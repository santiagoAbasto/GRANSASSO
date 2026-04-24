<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_gearbox_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('gearbox_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'gearbox_id'], 'product_gearbox_links_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_gearbox_links');
    }
};

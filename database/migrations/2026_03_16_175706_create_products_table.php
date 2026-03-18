<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('gearbox_id')->constrained()->cascadeOnDelete();

            $table->integer('figure')->nullable();
            $table->string('code_gs')->nullable();
            $table->string('code_oem')->nullable();

            $table->text('description');

            $table->string('image')->nullable();

            $table->string('extra_1')->nullable();
            $table->string('extra_2')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('desarrollos_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('phone', 50);
            $table->string('email', 200);
            $table->string('file_path', 500)->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desarrollos_messages');
    }
};

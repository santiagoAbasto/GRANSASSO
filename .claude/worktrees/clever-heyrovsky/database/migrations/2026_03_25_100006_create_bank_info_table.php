<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_info', function (Blueprint $table) {
            $table->id();
            $table->json('content');
            $table->timestamps();
        });

        // Seed one empty row
        \DB::table('bank_info')->insert([
            'content'    => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_info');
    }
};

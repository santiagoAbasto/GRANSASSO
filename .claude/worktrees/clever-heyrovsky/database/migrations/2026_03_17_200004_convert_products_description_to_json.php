<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convierte el texto existente al formato JSON multilingüe {es: "...", en: null, pt: null}
        DB::table('products')->whereNotNull('description')->orderBy("id")->each(function ($product) {
            DB::table('products')->where('id', $product->id)->update([
                'description' => json_encode([
                    'es' => $product->description,
                    'en' => null,
                    'pt' => null,
                ]),
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->json('description')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });
    }
};

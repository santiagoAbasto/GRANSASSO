<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('gearboxes')->whereNotNull('description')->orderBy("id")->each(function ($gearbox) {
            DB::table('gearboxes')->where('id', $gearbox->id)->update([
                'description' => json_encode([
                    'es' => $gearbox->description,
                    'en' => null,
                    'pt' => null,
                ]),
            ]);
        });

        Schema::table('gearboxes', function (Blueprint $table) {
            $table->json('description')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('gearboxes', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });
    }
};

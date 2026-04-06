<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            if (! Schema::hasColumn('sliders', 'video_url')) {
                $table->string('video_url')->nullable()->after('image');
            }

            if (! Schema::hasColumn('sliders', 'video_file')) {
                $table->string('video_file')->nullable()->after('video_url');
            }
        });

        Schema::table('sliders', function (Blueprint $table) {
            $table->string('image')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            if (Schema::hasColumn('sliders', 'video_file')) {
                $table->dropColumn('video_file');
            }

            if (Schema::hasColumn('sliders', 'video_url')) {
                $table->dropColumn('video_url');
            }
        });

        Schema::table('sliders', function (Blueprint $table) {
            $table->string('image')->nullable(false)->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_section_images', function (Blueprint $table) {
            if (! Schema::hasColumn('page_section_images', 'video_url')) {
                $table->string('video_url')->nullable()->after('image');
            }
            // Allow image to be nullable (slides can be image OR video)
            $table->string('image')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('page_section_images', function (Blueprint $table) {
            if (Schema::hasColumn('page_section_images', 'video_url')) {
                $table->dropColumn('video_url');
            }
            $table->string('image')->nullable(false)->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_sections', function (Blueprint $table) {
            $table->unsignedSmallInteger('autoplay_seconds')->default(3)->after('button_link');
        });

        Schema::table('page_section_images', function (Blueprint $table) {
            $table->string('kind', 20)->default('default')->after('video_url');
        });

        DB::table('page_sections')
            ->whereIn('type', ['about_main', 'calidad_main'])
            ->whereNull('autoplay_seconds')
            ->update(['autoplay_seconds' => 3]);

        $qualitySectionIds = DB::table('page_sections')
            ->where('type', 'calidad_main')
            ->pluck('id');

        if ($qualitySectionIds->isNotEmpty()) {
            DB::table('page_section_images')
                ->whereIn('section_id', $qualitySectionIds)
                ->update(['kind' => 'badge']);
        }

        $sections = DB::table('page_sections')
            ->whereIn('type', ['about_main', 'calidad_main'])
            ->whereNotNull('image')
            ->get(['id', 'type', 'image']);

        foreach ($sections as $section) {
            $hasGallery = DB::table('page_section_images')
                ->where('section_id', $section->id)
                ->where('kind', 'gallery')
                ->exists();

            if (! $hasGallery) {
                DB::table('page_section_images')->insert([
                    'section_id' => $section->id,
                    'image' => $section->image,
                    'video_url' => null,
                    'kind' => 'gallery',
                    'order' => 'A',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('page_sections')
            ->whereIn('type', ['about_main', 'calidad_main'])
            ->whereNotNull('image')
            ->update(['image' => null]);
    }

    public function down(): void
    {
        Schema::table('page_section_images', function (Blueprint $table) {
            $table->dropColumn('kind');
        });

        Schema::table('page_sections', function (Blueprint $table) {
            $table->dropColumn('autoplay_seconds');
        });
    }
};

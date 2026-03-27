<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar columnas JSON nuevas
        Schema::table('novedades', function (Blueprint $table) {
            $table->json('title_i18n')->nullable()->after('id');
            $table->json('excerpt_i18n')->nullable()->after('category');
            $table->json('body_i18n')->nullable()->after('excerpt_i18n');
        });

        // 2. Migrar datos existentes al nuevo formato JSON
        DB::statement("
            UPDATE novedades
            SET
                title_i18n   = JSON_OBJECT('es', IFNULL(title,''),   'en', IFNULL(title,''),   'pt', IFNULL(title,'')),
                excerpt_i18n = JSON_OBJECT('es', IFNULL(excerpt,''), 'en', IFNULL(excerpt,''), 'pt', IFNULL(excerpt,'')),
                body_i18n    = JSON_OBJECT('es', IFNULL(body,''),    'en', IFNULL(body,''),    'pt', IFNULL(body,''))
        ");

        // 3. Eliminar columnas viejas
        Schema::table('novedades', function (Blueprint $table) {
            $table->dropColumn(['title', 'excerpt', 'body']);
        });

        // 4. Renombrar columnas nuevas
        Schema::table('novedades', function (Blueprint $table) {
            $table->renameColumn('title_i18n',   'title');
            $table->renameColumn('excerpt_i18n', 'excerpt');
            $table->renameColumn('body_i18n',    'body');
        });
    }

    public function down(): void
    {
        Schema::table('novedades', function (Blueprint $table) {
            $table->string('title_old')->nullable()->after('id');
            $table->text('excerpt_old')->nullable()->after('category');
            $table->longText('body_old')->nullable()->after('excerpt_old');
        });

        DB::statement("
            UPDATE novedades
            SET
                title_old   = JSON_UNQUOTE(JSON_EXTRACT(title,   '$.es')),
                excerpt_old = JSON_UNQUOTE(JSON_EXTRACT(excerpt, '$.es')),
                body_old    = JSON_UNQUOTE(JSON_EXTRACT(body,    '$.es'))
        ");

        Schema::table('novedades', function (Blueprint $table) {
            $table->dropColumn(['title', 'excerpt', 'body']);
        });

        Schema::table('novedades', function (Blueprint $table) {
            $table->renameColumn('title_old',   'title');
            $table->renameColumn('excerpt_old', 'excerpt');
            $table->renameColumn('body_old',    'body');
        });
    }
};

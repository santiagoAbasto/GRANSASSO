<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visit_events', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 120)->nullable()->index();
            $table->string('ip_address', 45)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('path')->nullable()->index();
            $table->string('route_name')->nullable()->index();
            $table->string('locale', 5)->nullable()->index();
            $table->foreignId('private_user_id')->nullable()->constrained('private_users')->nullOnDelete();
            $table->timestamp('visited_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visit_events');
    }
};

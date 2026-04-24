<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('private_user_login_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('private_user_id')->constrained('private_users')->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('logged_in_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('private_user_login_events');
    }
};

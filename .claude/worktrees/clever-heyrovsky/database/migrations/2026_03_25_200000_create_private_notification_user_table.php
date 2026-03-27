<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('private_notification_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('private_notification_id')->constrained('private_notifications')->cascadeOnDelete();
            $table->foreignId('private_user_id')->constrained('private_users')->cascadeOnDelete();
            $table->unique(['private_notification_id', 'private_user_id'], 'notif_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('private_notification_user');
    }
};

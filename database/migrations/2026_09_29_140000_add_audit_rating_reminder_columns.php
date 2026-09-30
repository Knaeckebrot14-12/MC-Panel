<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('action', 64);
            $table->string('subject', 191)->nullable();
            $table->json('properties')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index('action');
            $table->index('created_at');
        });

        Schema::table('tickets', function (Blueprint $table) {
            // 1 = helpful, -1 = not helpful.
            $table->tinyInteger('rating')->nullable();
            $table->timestamp('rated_at')->nullable();
        });

        Schema::table('servers', function (Blueprint $table) {
            // The paid_with_coins_until value a renewal reminder was already sent for.
            $table->timestamp('coin_reminder_for')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('coin_reminder_for');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['rating', 'rated_at']);
        });

        Schema::dropIfExists('staff_audit_logs');
    }
};

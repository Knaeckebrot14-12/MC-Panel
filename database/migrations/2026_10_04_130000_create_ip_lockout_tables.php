<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // One row per failed login / 2FA attempt. Short lived (see p:security:cleanup).
        Schema::create('login_failures', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ip', 45);
            // What was typed into the username field (lower case), or the account of a failed 2FA code.
            $table->string('username', 191)->nullable();
            $table->string('type', 16)->default('login');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['ip', 'created_at']);
            $table->index('created_at');
        });

        // One row per block (automatic or manual). Active while blocked_until is in the future and
        // unblocked_at is empty. Rows stay for a while so repeated blocks can escalate.
        Schema::create('ip_blocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ip', 45);
            $table->string('reason', 16)->default('auto');
            $table->unsignedInteger('failures')->default(0);
            $table->text('usernames')->nullable();
            $table->timestamp('blocked_until');
            // No foreign keys on purpose: the history must outlive deleted staff accounts.
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamp('unblocked_at')->nullable();
            $table->unsignedInteger('unblocked_by')->nullable();
            // Set when the team was told about this block on Discord (also used for the rate limit).
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['ip', 'blocked_until']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_blocks');
        Schema::dropIfExists('login_failures');
    }
};

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_audit_logs', function (Blueprint $table) {
            // No foreign keys on purpose: the log must outlive deleted users and servers, so the
            // names are snapshotted next to the ids.
            $table->unsignedInteger('target_user_id')->nullable()->after('subject');
            $table->string('target_user', 191)->nullable()->after('target_user_id');
            $table->unsignedInteger('target_server_id')->nullable()->after('target_user');
            $table->string('target_server', 191)->nullable()->after('target_server_id');
        });
    }

    public function down(): void
    {
        Schema::table('staff_audit_logs', function (Blueprint $table) {
            $table->dropColumn(['target_user_id', 'target_user', 'target_server_id', 'target_server']);
        });
    }
};

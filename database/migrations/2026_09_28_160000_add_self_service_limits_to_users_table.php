<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('server_memory_limit')->default(2048)->after('root_admin');
            $table->integer('server_disk_limit')->default(5120)->after('server_memory_limit');
            $table->integer('server_cpu_limit')->default(100)->after('server_disk_limit');
            $table->integer('server_backup_limit')->default(1)->after('server_cpu_limit');
            $table->integer('server_slots')->default(2)->after('server_backup_limit');
            $table->timestamp('suspended_at')->nullable()->after('server_slots');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'server_memory_limit',
                'server_disk_limit',
                'server_cpu_limit',
                'server_backup_limit',
                'server_slots',
                'suspended_at',
            ]);
        });
    }
};

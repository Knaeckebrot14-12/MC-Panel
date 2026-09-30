<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->string('registration_ip', 45)->nullable()->after('email_verified_at');
            $table->string('discord_id', 32)->nullable()->unique()->after('registration_ip');
            $table->string('discord_username', 64)->nullable()->after('discord_id');
            $table->index('registration_ip');
        });

        // Everybody who already has an account counts as verified; only new sign-ups confirm their address.
        DB::table('users')->update(['email_verified_at' => DB::raw('created_at')]);

        Schema::table('servers', function (Blueprint $table) {
            $table->unsignedSmallInteger('auto_backup_hours')->default(0);
            $table->timestamp('auto_backup_last_at')->nullable();
        });

        Schema::create('server_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->float('cpu')->default(0);
            $table->unsignedBigInteger('memory')->default(0);
            $table->unsignedSmallInteger('players')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['server_id', 'created_at']);
            $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_stats');

        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn(['auto_backup_hours', 'auto_backup_last_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['registration_ip']);
            $table->dropUnique(['discord_id']);
            $table->dropColumn(['email_verified_at', 'registration_ip', 'discord_id', 'discord_username']);
        });
    }
};

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        // Node monitoring: what Wings reports every minute, and which alerts were already sent.
        Schema::table('nodes', function (Blueprint $table) {
            $table->string('wings_version', 32)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->json('monitor_state')->nullable();
        });

        Schema::create('node_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('node_id');
            $table->float('cpu')->default(0);
            $table->unsignedBigInteger('memory_used')->default(0);
            $table->unsignedBigInteger('memory_total')->default(0);
            $table->unsignedBigInteger('disk_used')->default(0);
            $table->unsignedBigInteger('disk_total')->default(0);
            $table->float('load')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['node_id', 'created_at']);
            $table->foreign('node_id')->references('id')->on('nodes')->cascadeOnDelete();
        });

        // Minecraft software installed through the version changer ({"type":"paper","version":"1.21.4",...}).
        Schema::table('servers', function (Blueprint $table) {
            $table->json('software')->nullable();
        });

        // Subdomains (name.example.com) that point at a server through Cloudflare DNS.
        Schema::create('server_subdomains', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id')->unique();
            $table->string('name', 63);
            $table->string('domain', 191);
            $table->json('records');
            $table->timestamps();

            $table->unique(['name', 'domain']);
            $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
        });

        // Browser push subscriptions of the installable app (one per browser/device).
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->text('endpoint');
            $table->char('endpoint_hash', 64)->unique();
            $table->string('public_key', 255);
            $table->string('auth_token', 255);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
        Schema::dropIfExists('server_subdomains');
        Schema::dropIfExists('node_stats');
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('software');
        });
        Schema::table('nodes', function (Blueprint $table) {
            $table->dropColumn(['wings_version', 'last_seen_at', 'monitor_state']);
        });
    }
};

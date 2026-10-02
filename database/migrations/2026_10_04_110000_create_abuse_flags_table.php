<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Servers the abuse scan (p:abuse:scan) found suspicious. A flag is only a hint for the team: nothing
 * happens to the server until a staff member decides. One unresolved flag per server and type.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('abuse_flags', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->string('type', 16);
            $table->json('details')->nullable();
            $table->timestamp('first_seen_at')->useCurrent();
            $table->timestamp('last_seen_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedInteger('resolved_by')->nullable();

            $table->index(['server_id', 'type', 'resolved_at']);
            $table->index('resolved_at');
            $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
            $table->foreign('resolved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abuse_flags');
    }
};

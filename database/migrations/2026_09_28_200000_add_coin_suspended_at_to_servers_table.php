<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            // When a coin-funded server was first suspended for a missed
            // renewal — distinct from an admin-level suspension — so we know
            // how long it's been sitting unpaid and can eventually clean it up.
            $table->timestamp('coin_suspended_at')->nullable()->after('paid_with_coins_until');
        });
    }

    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('coin_suspended_at');
        });
    }
};

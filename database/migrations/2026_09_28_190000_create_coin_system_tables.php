<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('coins')->default(0)->after('must_change_password');
            $table->timestamp('last_afk_tick_at')->nullable()->after('coins');
        });

        Schema::table('servers', function (Blueprint $table) {
            $table->timestamp('paid_with_coins_until')->nullable()->after('owner_id');
        });

        Schema::create('coin_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->integer('amount');
            $table->string('type', 64);
            $table->string('description', 191)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('linkvertise_claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('token', 64)->unique();
            $table->unsignedInteger('coins');
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linkvertise_claims');
        Schema::dropIfExists('coin_transactions');

        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('paid_with_coins_until');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['coins', 'last_afk_tick_at']);
        });
    }
};

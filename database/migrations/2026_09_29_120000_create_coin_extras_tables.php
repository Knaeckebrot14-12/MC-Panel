<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coin_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->unsignedInteger('coins');
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('uses')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('active')->default(true);
            $table->string('note', 191)->nullable();
            $table->timestamps();
        });

        Schema::create('coin_voucher_redemptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('voucher_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('coins');
            $table->timestamps();

            $table->unique(['voucher_id', 'user_id']);
            $table->foreign('voucher_id')->references('id')->on('coin_vouchers')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('coin_server_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('description', 191)->nullable();
            $table->unsignedInteger('memory');
            $table->unsignedInteger('disk');
            $table->unsignedInteger('cpu');
            $table->unsignedInteger('backups')->default(0);
            $table->unsignedInteger('monthly_price');
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('servers', function (Blueprint $table) {
            $table->unsignedInteger('coin_monthly_price')->nullable()->after('paid_with_coins_until');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_daily_claim_at')->nullable();
            $table->unsignedInteger('daily_streak')->default(0);
            $table->string('referral_code', 16)->nullable()->unique();
            $table->unsignedInteger('referred_by')->nullable();
            $table->timestamp('referral_rewarded_at')->nullable();

            $table->foreign('referred_by')->references('id')->on('users')->onDelete('set null');
        });

        DB::table('users')->whereNull('referral_code')->orderBy('id')->each(function ($user) {
            do {
                $code = strtoupper(Str::random(8));
            } while (DB::table('users')->where('referral_code', $code)->exists());

            DB::table('users')->where('id', $user->id)->update(['referral_code' => $code]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by']);
            $table->dropColumn(['last_daily_claim_at', 'daily_streak', 'referral_code', 'referred_by', 'referral_rewarded_at']);
        });

        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('coin_monthly_price');
        });

        Schema::dropIfExists('coin_server_plans');
        Schema::dropIfExists('coin_voucher_redemptions');
        Schema::dropIfExists('coin_vouchers');
    }
};

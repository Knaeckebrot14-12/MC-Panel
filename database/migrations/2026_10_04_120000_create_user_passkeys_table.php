<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Passkeys (WebAuthn credentials) users can sign in with instead of a password.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_passkeys', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('name', 64);
            // base64url of the credential id. Binary collation: base64url is case sensitive, so
            // "Ab" and "aB" are different credentials and must neither collide nor match each other.
            $table->string('credential_id', 512)->charset('ascii')->collation('ascii_bin')->unique();
            // PEM encoded public key.
            $table->text('public_key');
            $table->unsignedBigInteger('sign_count')->default(0);
            $table->json('transports')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('last_used_at')->nullable();

            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_passkeys');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nodes', function (Blueprint $table) {
            // Null means "no limit". This only gates self-service server
            // creation — admins can still create servers on the node past
            // this count from the admin area.
            $table->unsignedInteger('maximum_servers')->nullable()->after('disk_overallocate');
        });
    }

    public function down(): void
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->dropColumn('maximum_servers');
        });
    }
};

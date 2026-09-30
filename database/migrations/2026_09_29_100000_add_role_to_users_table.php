<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('user')->after('root_admin');
        });

        DB::table('users')->where('root_admin', 1)->update(['role' => 'admin']);

        $firstAdmin = DB::table('users')->where('root_admin', 1)->orderBy('id')->value('id');
        if ($firstAdmin) {
            DB::table('users')->where('id', $firstAdmin)->update(['role' => 'owner']);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};

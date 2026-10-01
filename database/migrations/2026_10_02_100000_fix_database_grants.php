<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        // Best effort: database hosts that are offline right now are repaired by the daily run
        // of the same command, so this never holds an update up.
        try {
            Artisan::call('p:databases:fix-grants');
        } catch (\Throwable) {
        }
    }

    public function down(): void
    {
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Episode durations move from minutes to seconds. The minutes column stays for one
     * release so a rollback loses nothing; a later migration drops it. Safe to run again:
     * it only converts rows that have minutes and no seconds yet.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('episodes', 'duration_seconds')) {
            Schema::table('episodes', function (Blueprint $table) {
                $table->unsignedInteger('duration_seconds')
                    ->nullable();
            });
        }

        DB::table('episodes')
            ->whereNotNull('duration_minutes')
            ->whereNull('duration_seconds')
            ->update(['duration_seconds' => DB::raw('duration_minutes * 60')]);
    }
};

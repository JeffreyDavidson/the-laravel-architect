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
     * Episode durations now live in `duration_seconds`. This drops the old minutes column,
     * after converting any row the earlier backfill could have missed so nothing is lost.
     * Safe to run again once the column is gone.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('episodes', 'duration_minutes')) {
            return;
        }

        DB::table('episodes')
            ->whereNotNull('duration_minutes')
            ->whereNull('duration_seconds')
            ->update(['duration_seconds' => DB::raw('duration_minutes * 60')]);

        Schema::table('episodes', function (Blueprint $table) {
            $table->dropColumn('duration_minutes');
        });
    }
};

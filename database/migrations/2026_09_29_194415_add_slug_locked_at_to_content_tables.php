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
     * Slugs are locked once content has been published, so a public URL is never edited by
     * hand. Content that is already live or scheduled is locked here from its publish date.
     * Safe to run again: it only fills rows that are live and not yet locked.
     */
    public function up(): void
    {
        foreach (['posts', 'projects', 'episodes', 'newsletter_issues'] as $table) {
            if (! Schema::hasColumn($table, 'slug_locked_at')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->timestamp('slug_locked_at')
                        ->nullable();
                });
            }

            $lockedAt = Schema::hasColumn($table, 'published_at')
                ? 'COALESCE(published_at, updated_at, created_at)'
                : 'COALESCE(updated_at, created_at)';

            DB::table($table)
                ->whereIn('status', ['published', 'scheduled'])
                ->whereNull('slug_locked_at')
                ->update(['slug_locked_at' => DB::raw($lockedAt)]);
        }
    }
};

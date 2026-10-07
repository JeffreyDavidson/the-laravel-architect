<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * newsletter_deliveries.subscriber_id and episode_post.episode_id were each covered only as the
     * second column of a composite index, so a cascading delete of a subscriber or an episode
     * scanned the whole table for every deleted row. On SQLite an index is a plain CREATE INDEX:
     * neither table is rebuilt, so no constraint can be lost. Each index is skipped when it
     * already exists, so the migration is safe to run again.
     */
    public function up(): void
    {
        foreach (['newsletter_deliveries' => 'subscriber_id', 'episode_post' => 'episode_id'] as $tableName => $column) {
            if (Schema::hasIndex($tableName, [$column])) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($column): void {
                $table->index($column);
            });
        }
    }
};

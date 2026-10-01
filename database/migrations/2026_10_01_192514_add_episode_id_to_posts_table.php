<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A post can point at the episode it relates to. The link is optional and is cleared, not
     * cascaded, when the episode is permanently deleted. Safe to run again.
     *
     * This uses a plain ADD COLUMN instead of the schema builder's foreign key helper: on SQLite
     * the helper rebuilds the whole posts table, which silently drops its status CHECK constraint.
     */
    public function up(): void
    {
        if (Schema::hasColumn('posts', 'episode_id')) {
            return;
        }

        DB::statement('ALTER TABLE posts ADD COLUMN episode_id INTEGER NULL REFERENCES episodes (id) ON DELETE SET NULL');
    }
};

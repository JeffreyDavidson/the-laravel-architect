<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A post can relate to several episodes, and an episode to several posts. A link is removed
     * when either side is permanently deleted. This creates a new table, so the existing posts
     * and episodes tables, with their CHECK constraints, are left untouched.
     */
    public function up(): void
    {
        Schema::create('episode_post', function (Blueprint $table) {
            $table->foreignId('post_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('episode_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->primary(['post_id', 'episode_id']);
        });
    }
};

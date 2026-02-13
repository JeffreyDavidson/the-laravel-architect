<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->integer('episode_number')->nullable();
            $table->integer('season_number')->default(1);
            $table->text('description');
            $table->longText('show_notes')->nullable();
            $table->string('audio_url')->nullable();
            $table->string('audio_file')->nullable();
            $table->string('embed_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('featured_image')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('guest_name')->nullable();
            $table->string('guest_title')->nullable();
            $table->string('guest_url')->nullable();
            $table->enum('status', ['draft', 'published', 'scheduled'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('episode_tag', function (Blueprint $table) {
            $table->foreignId('episode_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['episode_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('episode_tag');
        Schema::dropIfExists('episodes');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('social_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 32);
            $table->string('label')->nullable();
            $table->string('url', 2048);
            $table->boolean('is_enabled')->default(true);
            $table->boolean('show_in_footer')->default(true);
            $table->boolean('show_on_contact')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $timestamp = now();

        DB::table('social_profiles')->insert([
            [
                'platform' => 'github',
                'label' => null,
                'url' => 'https://github.com/JeffreyDavidson',
                'is_enabled' => true,
                'show_in_footer' => true,
                'show_on_contact' => true,
                'sort_order' => 10,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'platform' => 'x',
                'label' => '@thelaravelarch',
                'url' => 'https://x.com/thelaravelarch',
                'is_enabled' => true,
                'show_in_footer' => true,
                'show_on_contact' => true,
                'sort_order' => 20,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'platform' => 'youtube',
                'label' => null,
                'url' => 'https://youtube.com/@thelaravelarchitect',
                'is_enabled' => true,
                'show_in_footer' => true,
                'show_on_contact' => true,
                'sort_order' => 30,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'platform' => 'bluesky',
                'label' => null,
                'url' => 'https://bsky.app/profile/thelaravelarch',
                'is_enabled' => true,
                'show_in_footer' => true,
                'show_on_contact' => true,
                'sort_order' => 40,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'platform' => 'instagram',
                'label' => null,
                'url' => 'https://instagram.com/thelaravelarch',
                'is_enabled' => true,
                'show_in_footer' => true,
                'show_on_contact' => false,
                'sort_order' => 50,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'platform' => 'facebook',
                'label' => null,
                'url' => 'https://facebook.com/thelaravelarch',
                'is_enabled' => true,
                'show_in_footer' => true,
                'show_on_contact' => false,
                'sort_order' => 60,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ]);
    }
};

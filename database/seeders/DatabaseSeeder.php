<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'jeffrey@thelaravelarchitect.com'],
            [
                'name' => 'Jeffrey Davidson',
                'password' => \Illuminate\Support\Facades\Hash::make('change-me-immediately'),
            ]
        );

        $this->call([
            BlogSeeder::class,
            PodcastSeeder::class,
            EpisodeSeeder::class,
            ProjectSeeder::class,
        ]);

        // Clear featured images so CSS card art is used
        \App\Models\Post::query()->update(['featured_image' => null]);
    }
}

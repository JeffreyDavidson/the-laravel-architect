<?php

namespace Database\Seeders;

use App\Models\Podcast;
use Illuminate\Database\Seeder;

class PodcastSeeder extends Seeder
{
    public function run(): void
    {
        Podcast::create([
            'name' => 'Coffee with The Laravel Architect',
            'slug' => 'coffee-with-the-laravel-architect',
            'description' => 'Deep dives into Laravel, PHP, architecture patterns, and conversations with the people building the modern web.',
            'long_description' => "Grab a cup of coffee and join Jeffrey Davidson for honest conversations about building web applications with Laravel. From architecture decisions and testing strategies to career growth and the developer life — no fluff, just real talk from the trenches.\n\nWhether you're a senior dev refining your craft or just getting started with Laravel, each episode breaks down the patterns, tools, and mindset that separate good code from great code.",
            'cover_image' => null,
            'color' => '#4A7FBF',
            'apple_url' => null,
            'spotify_url' => null,
            'rss_url' => null,
            'youtube_url' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Podcast::create([
            'name' => 'Embracing Cloudy Days',
            'slug' => 'embracing-cloudy-days',
            'description' => 'A mental health podcast about finding strength in the storms. Honest conversations about the hard days and how we get through them.',
            'long_description' => "Life isn't always sunshine. Embracing Cloudy Days is Jeffrey Davidson's personal podcast exploring mental health, resilience, and the messy reality of being human in a world that expects you to always be fine.\n\nFrom managing stress as a developer and parent to navigating the weight of daily life with a special needs child — these are the conversations we don't have enough. Raw, real, and judgment-free.",
            'cover_image' => null,
            'color' => '#9D5175',
            'apple_url' => null,
            'spotify_url' => null,
            'rss_url' => null,
            'youtube_url' => null,
            'is_active' => true,
            'sort_order' => 2,
        ]);
    }
}

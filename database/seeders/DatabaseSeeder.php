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
            ['email' => 'thelaravelarchitect@gmail.com'],
            [
                'name' => 'Jeffrey Davidson',
                'password' => \Illuminate\Support\Facades\Hash::make('change-me-immediately'),
            ]
        );

        $this->call([
            BlogSeeder::class,
            PodcastSeeder::class,
            ProjectSeeder::class,
        ]);
    }
}

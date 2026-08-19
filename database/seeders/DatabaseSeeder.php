<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $jeffrey = User::firstOrCreate(
            ['email' => 'thelaravelarchitect@gmail.com'],
            [
                'name' => 'Jeffrey Davidson',
                'password' => Str::random(64),
            ]
        );
        $jeffrey->forceFill(['is_admin' => true])->save();

        $this->call([
            BlogSeeder::class,
            PodcastSeeder::class,
            ProjectSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        // Create admin user
        $jeffrey = User::firstOrCreate(
            ['email' => 'thelaravelarchitect@gmail.com'],
            [
                'name' => 'Jeffrey Davidson',
                'password' => Str::random(64),
            ]
        );
        $jeffrey->forceFill(['is_admin' => true])->save();
        $jeffrey->assignRole('super_admin');

        $this->call([
            ShieldSeeder::class,
            BlogSeeder::class,
            PodcastSeeder::class,
            ProjectSeeder::class,
        ]);
    }
}

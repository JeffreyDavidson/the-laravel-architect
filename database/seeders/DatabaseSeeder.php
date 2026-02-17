<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $reviewer = Role::firstOrCreate(['name' => 'reviewer', 'guard_name' => 'web']);

        // Create admin user
        $jeffrey = User::firstOrCreate(
            ['email' => 'thelaravelarchitect@gmail.com'],
            [
                'name' => 'Jeffrey Davidson',
                'password' => \Illuminate\Support\Facades\Hash::make('change-me-immediately'),
            ]
        );
        $jeffrey->assignRole('super_admin');

        $this->call([
            ShieldSeeder::class,
            BlogSeeder::class,
            PodcastSeeder::class,
            ProjectSeeder::class,
        ]);
    }
}

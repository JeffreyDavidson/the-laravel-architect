<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    config()->set('app.admin_email', 'admin@example.test');
});

it('does not create users with the former known seeder passwords', function () {
    $this->seed(DatabaseSeeder::class);

    $admin = User::query()
        ->where('email', 'admin@example.test')
        ->sole();
    expect(Hash::check('change-me-immediately', $admin->password))->toBeFalse()
        ->and(User::query()->count())->toBe(1)
        ->and($admin->is_admin)->toBeTrue();
});

it('preserves the existing admin password when the seeder runs', function () {
    $admin = User::factory()->create([
        'email' => 'admin@example.test',
        'password' => 'chosen-admin-password',
    ]);
    $this->seed(DatabaseSeeder::class);

    expect(Hash::check('chosen-admin-password', $admin->refresh()->password))->toBeTrue()
        ->and($admin->is_admin)->toBeTrue();
});

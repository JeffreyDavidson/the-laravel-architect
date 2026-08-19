<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('does not create users with the former known seeder passwords', function () {
    $this->seed(DatabaseSeeder::class);

    $admin = User::query()
        ->where('email', 'thelaravelarchitect@gmail.com')
        ->sole();
    $author = User::query()
        ->where('email', 'jeffrey@thelaravelarchitect.com')
        ->sole();

    expect(Hash::check('change-me-immediately', $admin->password))->toBeFalse()
        ->and(Hash::check('temporary-password-change-me', $author->password))->toBeFalse()
        ->and($admin->hasRole('super_admin'))->toBeTrue();
});

it('preserves existing user passwords when seeders run', function () {
    $admin = User::factory()->create([
        'email' => 'thelaravelarchitect@gmail.com',
        'password' => 'chosen-admin-password',
    ]);
    $author = User::factory()->create([
        'email' => 'jeffrey@thelaravelarchitect.com',
        'password' => 'chosen-author-password',
    ]);

    $this->seed(DatabaseSeeder::class);

    expect(Hash::check('chosen-admin-password', $admin->refresh()->password))->toBeTrue()
        ->and(Hash::check('chosen-author-password', $author->refresh()->password))->toBeTrue()
        ->and($admin->hasRole('super_admin'))->toBeTrue();
});

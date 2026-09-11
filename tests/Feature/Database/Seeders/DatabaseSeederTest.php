<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('app.admin_email', 'admin@example.test');
    config()->set('app.content_author_email', 'author@example.test');
});

it('does not create users with the former known seeder passwords', function () {
    $this->seed(DatabaseSeeder::class);

    $admin = User::query()
        ->where('email', 'admin@example.test')
        ->sole();
    $author = User::query()
        ->where('email', 'author@example.test')
        ->sole();

    expect(Hash::check('change-me-immediately', $admin->password))->toBeFalse()
        ->and(Hash::check('temporary-password-change-me', $author->password))->toBeFalse()
        ->and($admin->is_admin)->toBeTrue();
});

it('preserves existing user passwords when seeders run', function () {
    $admin = User::factory()->create([
        'email' => 'admin@example.test',
        'password' => 'chosen-admin-password',
    ]);
    $author = User::factory()->create([
        'email' => 'author@example.test',
        'password' => 'chosen-author-password',
    ]);

    $this->seed(DatabaseSeeder::class);

    expect(Hash::check('chosen-admin-password', $admin->refresh()->password))->toBeTrue()
        ->and(Hash::check('chosen-author-password', $author->refresh()->password))->toBeTrue()
        ->and($admin->is_admin)->toBeTrue();
});

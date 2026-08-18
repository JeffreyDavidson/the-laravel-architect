<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('only admits users with an admin panel role', function () {
    $user = User::factory()->create();
    $panel = Filament::getPanel('admin');

    expect($user->canAccessPanel($panel))->toBeFalse();

    Role::query()->create(['name' => 'reviewer', 'guard_name' => 'web']);
    $user->assignRole('reviewer');

    expect($user->canAccessPanel($panel))->toBeTrue();
});

<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('only admits administrators to the admin panel', function () {
    $user = User::factory()->create();
    $panel = Filament::getPanel('admin');

    expect($user->canAccessPanel($panel))->toBeFalse();

    $user->forceFill(['is_admin' => true])->save();

    expect($user->canAccessPanel($panel))->toBeTrue();
});

it('rejects a non-administrator at the admin panel boundary', function () {
    $user = User::factory()->create();
    $panel = Filament::getPanel('admin');

    expect($user->canAccessPanel($panel))->toBeFalse();

    $this->actingAs($user)
        ->get($panel->getUrl())
        ->assertForbidden();
});

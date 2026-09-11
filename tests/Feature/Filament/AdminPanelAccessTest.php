<?php

use App\Models\User;
use Filament\Auth\Pages\EditProfile;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
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
        ->get(Dashboard::getUrl(panel: 'admin'))
        ->assertForbidden();
});

it('allows an administrator to manage their profile', function () {
    $administrator = User::factory()->create(['is_admin' => true]);
    $profileUrl = EditProfile::getUrl(panel: 'admin');

    expect(Filament::getPanel('admin')->getProfileUrl())->toBe($profileUrl);

    $this->actingAs($administrator)
        ->get($profileUrl)
        ->assertOk();
});

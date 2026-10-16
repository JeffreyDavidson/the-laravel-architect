<?php

use App\Enums\SocialPlatform;
use App\Models\SocialProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\SocialProfiles\Pages\ListSocialProfiles;
use JeffreyDavidson\CreatorKit\Filament\Resources\SocialProfiles\SocialProfileResource;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('allows administrators to manage social profiles', function () {
    actingAs(User::factory()->create(['is_admin' => true]))
        ->get(SocialProfileResource::getUrl('index'))
        ->assertOk()
        ->assertSee('GitHub');
});

it('forbids non-administrators from managing social profiles', function () {
    actingAs(User::factory()->create())
        ->get(SocialProfileResource::getUrl('index'))
        ->assertForbidden();
});

it('lists each profile under its platform label', function () {
    actingAs(User::factory()->create(['is_admin' => true]));
    $profile = SocialProfile::query()
        ->where('platform', SocialPlatform::X->value)
        ->firstOrFail();

    livewire(ListSocialProfiles::class)
        ->assertTableColumnFormattedStateSet('platform', 'X / Twitter', $profile);
});

it('renders the social profile create page for an administrator', function () {
    // Arrange
    actingAs(User::factory()->create(['is_admin' => true]));

    // Act
    $response = get(SocialProfileResource::getUrl('create'));

    // Assert
    $response->assertOk();
});

it('renders the social profile edit page for an administrator', function () {
    // Arrange
    $profile = SocialProfile::factory()->create(['label' => 'Personal site']);
    actingAs(User::factory()->create(['is_admin' => true]));

    // Act
    $response = get(SocialProfileResource::getUrl('edit', ['record' => $profile]));

    // Assert
    $response->assertOk()
        ->assertSee('Personal site');
});

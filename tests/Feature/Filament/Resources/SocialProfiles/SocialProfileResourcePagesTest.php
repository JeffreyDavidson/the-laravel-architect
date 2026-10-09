<?php

use App\Enums\SocialPlatform;
use App\Models\SocialProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\SocialProfiles\Pages\ListSocialProfiles;
use JeffreyDavidson\CreatorKit\Filament\Resources\SocialProfiles\SocialProfileResource;

use function Pest\Laravel\actingAs;
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

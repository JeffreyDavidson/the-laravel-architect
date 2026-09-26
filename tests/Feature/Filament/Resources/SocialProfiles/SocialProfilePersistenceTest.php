<?php

use App\Enums\SocialPlatform;
use App\Filament\Resources\SocialProfiles\Pages\CreateSocialProfile;
use App\Filament\Resources\SocialProfiles\Pages\EditSocialProfile;
use App\Models\SocialProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
});

it('creates a social profile with its public visibility settings', function () {
    livewire(CreateSocialProfile::class)
        ->fillForm([
            'platform' => SocialPlatform::Other->value,
            'label' => 'Threads',
            'url' => 'https://www.threads.net/@thelaravelarchitect',
            'is_enabled' => true,
            'show_in_footer' => true,
            'show_on_contact' => false,
            'sort_order' => 70,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $profile = SocialProfile::query()
        ->where('url', 'https://www.threads.net/@thelaravelarchitect')
        ->sole();

    expect($profile)
        ->platform->toBe(SocialPlatform::Other)
        ->label->toBe('Threads')
        ->is_enabled->toBeTrue()
        ->show_in_footer->toBeTrue()
        ->show_on_contact->toBeFalse()
        ->sort_order->toBe(70);
});

it('updates the profile URL and visibility through Filament', function () {
    $profile = SocialProfile::query()
        ->where('platform', SocialPlatform::X->value)
        ->firstOrFail();

    livewire(EditSocialProfile::class, ['record' => $profile->getRouteKey()])
        ->fillForm([
            'platform' => SocialPlatform::X->value,
            'label' => '@updated-handle',
            'url' => 'https://x.com/updated-handle',
            'is_enabled' => false,
            'show_in_footer' => true,
            'show_on_contact' => false,
            'sort_order' => 25,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($profile->refresh())
        ->label->toBe('@updated-handle')
        ->url->toBe('https://x.com/updated-handle')
        ->is_enabled->toBeFalse()
        ->show_on_contact->toBeFalse()
        ->sort_order->toBe(25);
});

it('rejects social profile URLs that are not HTTPS', function () {
    livewire(CreateSocialProfile::class)
        ->fillForm([
            'platform' => SocialPlatform::Instagram->value,
            'url' => 'http://instagram.com/thelaravelarch',
        ])
        ->call('create')
        ->assertHasFormErrors(['url']);

    expect(SocialProfile::query()->where('url', 'http://instagram.com/thelaravelarch')
        ->exists())
        ->toBeFalse();
});

it('requires a display label for other platforms', function () {
    livewire(CreateSocialProfile::class)
        ->fillForm([
            'platform' => SocialPlatform::Other->value,
            'url' => 'https://threads.net/@thelaravelarchitect',
        ])
        ->call('create')
        ->assertHasFormErrors(['label' => 'required']);
});

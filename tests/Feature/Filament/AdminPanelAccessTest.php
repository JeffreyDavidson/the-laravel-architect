<?php

use App\Filament\Pages\MediaHealth;
use App\Models\User;
use Filament\Auth\Pages\EditProfile;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\Pages\EditPost;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\PostResource;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('only admits administrators to the admin panel', function () {
    $user = User::factory()->create();
    $panel = Filament::getPanel('admin');

    expect($user->canAccessPanel($panel))->toBeFalse();

    $user->forceFill(['is_admin' => true])
        ->save();

    expect($user->canAccessPanel($panel))->toBeTrue();
});

it('rejects a non-administrator at the admin panel boundary', function () {
    $user = User::factory()->create();
    $panel = Filament::getPanel('admin');

    expect($user->canAccessPanel($panel))->toBeFalse();

    actingAs($user)
        ->get(Dashboard::getUrl(panel: 'admin'))
        ->assertForbidden();
});

it('rejects a non-administrator from resource and page URLs', function (string $url) {
    actingAs(User::factory()->create())
        ->get($url)
        ->assertForbidden();
})->with([
    'post list' => fn (): string => PostResource::getUrl('index', panel: 'admin'),
    'post editor' => fn (): string => PostResource::getUrl('edit', ['record' => PublishableFixtures::readyPost()], panel: 'admin'),
    'media health repairs' => fn (): string => MediaHealth::getUrl(panel: 'admin'),
]);

it('refuses a non-administrator the post editor behind the panel boundary', function () {
    $post = PublishableFixtures::readyPost();
    actingAs(User::factory()->create());

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertForbidden();
});

it('gives an administrator the post editor and its publish action', function () {
    $post = PublishableFixtures::readyPost();
    actingAs(User::factory()->create(['is_admin' => true]));

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertActionVisible('publish');
});

it('allows an administrator to manage their profile', function () {
    $administrator = User::factory()->create(['is_admin' => true]);
    $profileUrl = EditProfile::getUrl(panel: 'admin');

    expect(Filament::getPanel('admin')->getProfileUrl())->toBe($profileUrl);

    actingAs($administrator)
        ->get($profileUrl)
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Sign out');
});

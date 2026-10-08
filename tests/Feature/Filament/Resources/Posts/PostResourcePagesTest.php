<?php

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);
});

it('renders the post create page for an authorized user', function () {
    get(PostResource::getUrl('create'))
        ->assertOk();
});

it('renders the post edit page for an authorized user', function () {
    $post = Post::factory()->create();

    get(PostResource::getUrl('edit', ['record' => $post]))
        ->assertOk();
});

it('badges posts in review first, then drafts', function (int $inReview, int $drafts, ?string $badge, string $color) {
    Post::factory()
        ->count($inReview)
        ->inReview()
        ->create();
    Post::factory()
        ->count($drafts)
        ->create();
    Post::factory()
        ->published()
        ->create();

    expect(PostResource::getNavigationBadge())->toBe($badge)
        ->and(PostResource::getNavigationBadgeColor())
        ->toBe($color);
})->with([
    'posts in review' => [2, 3, '2 to review', 'info'],
    'one draft' => [0, 1, '1 draft', 'gray'],
    'several drafts' => [0, 2, '2 drafts', 'gray'],
    'nothing waiting' => [0, 0, null, 'gray'],
]);

it('updates the post badge as soon as a post enters review', function () {
    Post::factory()->create();
    expect(PostResource::getNavigationBadge())->toBe('1 draft');

    Post::factory()
        ->inReview()
        ->create();

    expect(PostResource::getNavigationBadge())->toBe('1 to review');
});

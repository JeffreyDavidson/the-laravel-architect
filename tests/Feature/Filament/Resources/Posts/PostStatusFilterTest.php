<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\Pages\ListPosts;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    actingAs(User::factory()->create(['is_admin' => true]));
});

it('filters posts by draft status', function () {
    $posts = collect([
        Post::factory()->create(),
        Post::factory()
            ->published()
            ->create(),
    ]);

    livewire(ListPosts::class)
        ->filterTable('status', PublishStatus::Draft)
        ->assertCanSeeTableRecords([$posts[0]])
        ->assertCanNotSeeTableRecords([$posts[1]]);
});

it('filters posts by in review status', function () {
    $posts = collect([
        Post::factory()
            ->inReview()
            ->create(),
        Post::factory()->create(),
    ]);

    livewire(ListPosts::class)
        ->filterTable('status', PublishStatus::InReview)
        ->assertCanSeeTableRecords([$posts[0]])
        ->assertCanNotSeeTableRecords([$posts[1]]);
});

it('filters posts by published status', function () {
    $posts = collect([
        Post::factory()
            ->published()
            ->create(),
        Post::factory()
            ->inReview()
            ->create(),
    ]);

    livewire(ListPosts::class)
        ->filterTable('status', PublishStatus::Published)
        ->assertCanSeeTableRecords([$posts[0]])
        ->assertCanNotSeeTableRecords([$posts[1]]);
});

<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
});

it('filters posts by draft status', function () {
    $posts = collect([
        Post::query()->create([
            'title' => 'Draft status filter result',
            'slug' => 'draft-status-filter-result',
            'content' => 'Post content.',
            'user_id' => auth()->id(),
            'status' => PublishStatus::Draft,
        ]),
        Post::query()->create([
            'title' => 'Published status filter exclusion',
            'slug' => 'published-status-filter-exclusion',
            'content' => 'Post content.',
            'user_id' => auth()->id(),
            'status' => PublishStatus::Published,
        ]),
    ]);

    livewire(ListPosts::class)
        ->filterTable('status', PublishStatus::Draft)
        ->assertCanSeeTableRecords([$posts[0]])
        ->assertCanNotSeeTableRecords([$posts[1]]);
});

it('filters posts by in review status', function () {
    $posts = collect([
        Post::query()->create([
            'title' => 'In review status filter result',
            'slug' => 'in-review-status-filter-result',
            'content' => 'Post content.',
            'user_id' => auth()->id(),
            'status' => PublishStatus::InReview,
        ]),
        Post::query()->create([
            'title' => 'Draft status filter exclusion',
            'slug' => 'draft-status-filter-exclusion',
            'content' => 'Post content.',
            'user_id' => auth()->id(),
            'status' => PublishStatus::Draft,
        ]),
    ]);

    livewire(ListPosts::class)
        ->filterTable('status', PublishStatus::InReview)
        ->assertCanSeeTableRecords([$posts[0]])
        ->assertCanNotSeeTableRecords([$posts[1]]);
});

it('filters posts by published status', function () {
    $posts = collect([
        Post::query()->create([
            'title' => 'Published status filter result',
            'slug' => 'published-status-filter-result',
            'content' => 'Post content.',
            'user_id' => auth()->id(),
            'status' => PublishStatus::Published,
        ]),
        Post::query()->create([
            'title' => 'In review status filter exclusion',
            'slug' => 'in-review-status-filter-exclusion',
            'content' => 'Post content.',
            'user_id' => auth()->id(),
            'status' => PublishStatus::InReview,
        ]),
    ]);

    livewire(ListPosts::class)
        ->filterTable('status', PublishStatus::Published)
        ->assertCanSeeTableRecords([$posts[0]])
        ->assertCanNotSeeTableRecords([$posts[1]]);
});

<?php

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
});

it('renders the post create page for an authorized user', function () {
    $this->get(PostResource::getUrl('create'))
        ->assertOk();
});

it('renders the post edit page for an authorized user', function () {
    $post = Post::query()->create([
        'title' => 'Post page coverage',
        'slug' => 'post-page-coverage',
        'content' => 'Post content',
        'user_id' => auth()->id(),
    ]);

    $this->get(PostResource::getUrl('edit', ['record' => $post]))
        ->assertOk();
});

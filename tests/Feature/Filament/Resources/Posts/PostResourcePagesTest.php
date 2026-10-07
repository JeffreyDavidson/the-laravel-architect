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

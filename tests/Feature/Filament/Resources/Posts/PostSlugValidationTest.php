<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ShieldSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $this->actingAs($user);
});

it('rejects non-normalized post slugs when creating a post', function (string $slug) {
    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Post title',
            'slug' => $slug,
            'content' => 'Post content',
            'status' => PublishStatus::Draft,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'regex']);

    expect(Post::query()->exists())->toBeFalse();
})->with([
    'path traversal' => '../post-title',
    'spaces' => 'post title',
    'uppercase characters' => 'Post-Title',
    'leading hyphen' => '-post-title',
    'trailing hyphen' => 'post-title-',
    'repeated hyphens' => 'post--title',
]);

it('accepts a normalized post slug when creating a post', function () {
    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Post title',
            'slug' => 'post-title-2',
            'content' => 'Post content',
            'status' => PublishStatus::Draft,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Post::query()->sole()->slug)->toBe('post-title-2');
});

it('rejects a non-normalized post slug when editing a post', function () {
    $post = Post::query()->create([
        'title' => 'Post title',
        'slug' => 'post-title',
        'content' => 'Post content',
        'user_id' => auth()->id(),
        'status' => PublishStatus::Draft,
    ]);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm(['slug' => '../post-title'])
        ->call('save')
        ->assertHasFormErrors(['slug' => 'regex']);

    expect($post->refresh()->slug)->toBe('post-title');
});

it('rejects a non-normalized slug when creating a category inline', function () {
    Livewire::test(CreatePost::class)
        ->callFormComponentAction('category_id', 'createOption', [
            'name' => 'Category name',
            'slug' => '../category-name',
        ])
        ->assertHasFormErrors(['slug' => 'regex']);

    expect(Category::query()->exists())->toBeFalse();
});

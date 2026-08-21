<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
});

it('rejects non-normalized post slugs when creating a post', function (string $slug) {
    livewire(CreatePost::class)
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
    livewire(CreatePost::class)
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

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm(['slug' => '../post-title'])
        ->call('save')
        ->assertHasFormErrors(['slug' => 'regex']);

    expect($post->refresh()->slug)->toBe('post-title');
});

it('rejects a non-normalized slug when creating a category inline', function () {
    livewire(CreatePost::class)
        ->callAction(TestAction::make('createOption')->schemaComponent('category_id'), data: [
            'name' => 'Category name',
            'slug' => '../category-name',
        ])
        ->assertHasFormErrors(['slug' => 'regex']);

    expect(Category::query()->exists())->toBeFalse();
});

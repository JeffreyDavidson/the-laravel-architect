<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\Categories\Pages\EditCategory;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    actingAs(User::factory()->create(['is_admin' => true]));
});

it('deletes a category through the edit page', function () {
    $category = Category::factory()->create();

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->callAction(DeleteAction::class);

    expect(Category::query()->find($category->id))->toBeNull();
});

it('keeps the published posts of a deleted category live without a category', function () {
    // Arrange
    $category = Category::factory()->create();
    $post = Post::factory()
        ->published()
        ->for($category)
        ->create();

    // Act
    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->callAction(DeleteAction::class);

    // Assert
    $post->refresh();

    expect($post->category_id)
        ->toBeNull()
        ->and($post->isPublished())
        ->toBeTrue();
});

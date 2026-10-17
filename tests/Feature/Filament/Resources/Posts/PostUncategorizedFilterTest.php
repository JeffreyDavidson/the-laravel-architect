<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\Pages\ListPosts;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    actingAs(User::factory()->create(['is_admin' => true]));
});

it('filters the posts table to posts without a category', function () {
    // Arrange
    $uncategorized = Post::factory()->create(['category_id' => null]);
    $categorized = Post::factory()->create();

    // Act
    $component = livewire(ListPosts::class)
        ->filterTable('uncategorized');

    // Assert
    $component->assertCanSeeTableRecords([$uncategorized])
        ->assertCanNotSeeTableRecords([$categorized]);
});

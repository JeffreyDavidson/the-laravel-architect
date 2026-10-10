<?php

use App\Models\Category;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\Categories\Pages\CreateCategory;
use JeffreyDavidson\CreatorKit\Filament\Resources\Categories\Pages\EditCategory;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\Pages\CreatePost;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);
});

it('rejects non-normalized category slugs when creating a category', function (string $slug) {
    livewire(CreateCategory::class)
        ->fillForm([
            'name' => 'Category name',
            'slug' => $slug,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'regex']);

    expect(Category::query()->exists())->toBeFalse();
})->with([
    'path traversal' => '../category-name',
    'spaces' => 'category name',
    'uppercase characters' => 'Category-Name',
    'leading hyphen' => '-category-name',
    'trailing hyphen' => 'category-name-',
    'repeated hyphens' => 'category--name',
]);

it('accepts a normalized category slug when creating a category', function () {
    livewire(CreateCategory::class)
        ->fillForm([
            'name' => 'Category name',
            'slug' => 'category-name-2',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Category::query()->sole()
        ->slug)->toBe('category-name-2');
});

it('rejects duplicate category slugs when creating a category', function () {
    Category::factory()->create(['slug' => 'category-name']);

    livewire(CreateCategory::class)
        ->fillForm([
            'name' => 'Category name',
            'slug' => 'category-name',
        ])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);

    expect(Category::query()->count())->toBe(1);
});

it('rejects a duplicate category slug when editing a category', function () {
    Category::factory()->create(['slug' => 'existing-category']);
    $category = Category::factory()->create(['slug' => 'category-name']);

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm(['slug' => 'existing-category'])
        ->call('save')
        ->assertHasFormErrors(['slug' => 'unique']);

    expect($category->refresh()
        ->slug)->toBe('category-name');
});

it('allows a category to retain its slug when editing', function () {
    $category = Category::factory()->create(['slug' => 'category-name']);

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm(['name' => 'Updated category name'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($category->refresh())
        ->name->toBe('Updated category name')
        ->slug->toBe('category-name');
});

it('rejects duplicate category slugs when creating a category inline', function () {
    Category::factory()->create(['slug' => 'category-name']);

    livewire(CreatePost::class)
        ->callAction(TestAction::make('createOption')->schemaComponent('category_id'), data: [
            'name' => 'Category name',
            'slug' => 'category-name',
        ])
        ->assertHasFormErrors(['slug' => 'unique']);

    expect(Category::query()->count())->toBe(1);
});

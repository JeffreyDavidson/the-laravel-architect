<?php

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ShieldSeeder::class);

    $user = User::factory()->create(['is_admin' => true]);
    $user->assignRole('super_admin');
    $this->actingAs($user);
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

    expect(Category::query()->sole()->slug)->toBe('category-name-2');
});

it('rejects duplicate category slugs when creating a category', function () {
    Category::query()->create([
        'name' => 'Existing category',
        'slug' => 'category-name',
    ]);

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
    Category::query()->create([
        'name' => 'Existing category',
        'slug' => 'existing-category',
    ]);
    $category = Category::query()->create([
        'name' => 'Category name',
        'slug' => 'category-name',
    ]);

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm(['slug' => 'existing-category'])
        ->call('save')
        ->assertHasFormErrors(['slug' => 'unique']);

    expect($category->refresh()->slug)->toBe('category-name');
});

it('allows a category to retain its slug when editing', function () {
    $category = Category::query()->create([
        'name' => 'Category name',
        'slug' => 'category-name',
    ]);

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm(['name' => 'Updated category name'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($category->refresh())
        ->name->toBe('Updated category name')
        ->slug->toBe('category-name');
});

it('rejects duplicate category slugs when creating a category inline', function () {
    Category::query()->create([
        'name' => 'Existing category',
        'slug' => 'category-name',
    ]);

    livewire(CreatePost::class)
        ->callFormComponentAction('category_id', 'createOption', [
            'name' => 'Category name',
            'slug' => 'category-name',
        ])
        ->assertHasFormErrors(['slug' => 'unique']);

    expect(Category::query()->count())->toBe(1);
});

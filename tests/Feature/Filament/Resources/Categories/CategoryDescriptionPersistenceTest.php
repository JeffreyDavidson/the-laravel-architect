<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\Categories\Pages\CreateCategory;
use JeffreyDavidson\CreatorKit\Filament\Resources\Categories\Pages\EditCategory;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);
});

it('persists a category description when creating a category', function () {
    livewire(CreateCategory::class)
        ->fillForm([
            'name' => 'Architecture',
            'slug' => 'architecture',
            'description' => 'Articles about application architecture.',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Category::query()->sole()
        ->description)
        ->toBe('Articles about application architecture.');
});

it('persists a category description when editing a category', function () {
    $category = Category::factory()->create(['description' => 'Original description.']);

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm(['description' => 'Updated description.'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($category->refresh()
        ->description)->toBe('Updated description.');
});

it('renders the persisted description on the public category page', function () {
    $category = Category::factory()->create(['description' => 'Articles about application architecture.']);

    get(route('blog.category', $category))
        ->assertOk()
        ->assertSee('Articles about application architecture.');
});

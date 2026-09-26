<?php

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
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
    $category = Category::query()->create([
        'name' => 'Architecture',
        'slug' => 'architecture',
        'description' => 'Original description.',
    ]);

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm(['description' => 'Updated description.'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($category->refresh()
        ->description)->toBe('Updated description.');
});

it('renders the persisted description on the public category page', function () {
    $category = Category::query()->create([
        'name' => 'Architecture',
        'slug' => 'architecture',
        'description' => 'Articles about application architecture.',
    ]);

    $this->get(route('blog.category', $category))
        ->assertOk()
        ->assertSee('Articles about application architecture.');
});

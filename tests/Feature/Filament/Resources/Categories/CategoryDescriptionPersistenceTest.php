<?php

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\BlogSeeder;
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

it('persists a category description when creating a category', function () {
    livewire(CreateCategory::class)
        ->fillForm([
            'name' => 'Architecture',
            'slug' => 'architecture',
            'description' => 'Articles about application architecture.',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Category::query()->sole()->description)
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

    expect($category->refresh()->description)->toBe('Updated description.');
});

it('creates and refreshes category descriptions from the blog seeder', function () {
    Category::query()->create([
        'name' => 'Personal',
        'slug' => 'personal',
        'description' => null,
    ]);

    $this->seed(BlogSeeder::class);

    expect(Category::query()->where('name', 'Personal')->value('description'))
        ->toBe('Personal stories, reflections, and life updates.')
        ->and(Category::query()->where('name', 'Career')->value('description'))
        ->toBe('Career advice, lessons learned, and professional growth.')
        ->and(Category::query()->where('name', 'Laravel')->value('description'))
        ->toBe('Laravel tutorials, opinions, and deep dives.');
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

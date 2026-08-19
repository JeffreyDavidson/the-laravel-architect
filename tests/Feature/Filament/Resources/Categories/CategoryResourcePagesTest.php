<?php

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
});

it('renders the category create page for an authorized user', function () {
    $this->get(CategoryResource::getUrl('create'))
        ->assertOk();
});

it('renders the category edit page for an authorized user', function () {
    $category = Category::query()->create([
        'name' => 'Category page coverage',
        'slug' => 'category-page-coverage',
        'description' => 'Category description',
    ]);

    $this->get(CategoryResource::getUrl('edit', ['record' => $category]))
        ->assertOk();
});

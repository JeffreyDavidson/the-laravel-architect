<?php

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);
});

it('renders the category create page for an authorized user', function () {
    get(CategoryResource::getUrl('create'))
        ->assertOk();
});

it('renders the category edit page for an authorized user', function () {
    $category = Category::factory()->create();

    get(CategoryResource::getUrl('edit', ['record' => $category]))
        ->assertOk();
});

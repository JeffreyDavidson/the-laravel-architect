<?php

use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Models\Category;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
});

it('deletes a category through the edit page', function () {
    $category = Category::query()->create([
        'name' => 'Category delete coverage',
        'slug' => 'category-delete-coverage',
        'description' => 'Category description',
    ]);

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->callAction(DeleteAction::class);

    expect(Category::query()->find($category->id))->toBeNull();
});

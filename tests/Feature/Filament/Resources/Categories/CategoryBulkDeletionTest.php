<?php

use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
});

it('deletes selected categories through the table bulk action', function () {
    $categories = collect([
        Category::query()->create(['name' => 'First category', 'slug' => 'first-category']),
        Category::query()->create(['name' => 'Second category', 'slug' => 'second-category']),
    ]);

    livewire(ListCategories::class)
        ->callTableBulkAction(DeleteBulkAction::class, $categories);

    expect(Category::query()->whereKey($categories->pluck('id'))->count())->toBe(0);
});

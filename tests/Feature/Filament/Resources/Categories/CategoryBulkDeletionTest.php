<?php

use App\Models\Category;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\Categories\Pages\ListCategories;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    actingAs(User::factory()->create(['is_admin' => true]));
});

it('deletes selected categories through the table bulk action', function () {
    $categories = Category::factory()
        ->count(2)
        ->create();

    livewire(ListCategories::class)
        ->selectTableRecords($categories)
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()
            ->bulk());

    expect(Category::query()->whereKey($categories->pluck('id'))
        ->count())->toBe(0);
});

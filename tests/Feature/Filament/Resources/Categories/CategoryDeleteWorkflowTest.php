<?php

use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Models\Category;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    actingAs(User::factory()->create(['is_admin' => true]));
});

it('deletes a category through the edit page', function () {
    $category = Category::factory()->create();

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->callAction(DeleteAction::class);

    expect(Category::query()->find($category->id))->toBeNull();
});

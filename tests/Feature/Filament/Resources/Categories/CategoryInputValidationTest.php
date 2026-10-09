<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\Categories\Pages\CreateCategory;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('rejects category names longer than the database column', function () {
    actingAs(User::factory()->create(['is_admin' => true]));

    livewire(CreateCategory::class)
        ->fillForm([
            'name' => str_repeat('a', 256),
            'slug' => 'oversized-category-name',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'max']);

    expect(Category::query()->exists())->toBeFalse();
});

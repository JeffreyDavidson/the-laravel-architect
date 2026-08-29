<?php

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

it('rejects category names longer than the database column', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));

    livewire(CreateCategory::class)
        ->fillForm([
            'name' => str_repeat('a', 256),
            'slug' => 'oversized-category-name',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'max']);

    expect(Category::query()->exists())->toBeFalse();
});

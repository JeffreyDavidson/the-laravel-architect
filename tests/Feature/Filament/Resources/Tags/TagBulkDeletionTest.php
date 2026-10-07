<?php

use App\Filament\Resources\Tags\Pages\ListTags;
use App\Models\Tag;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    actingAs(User::factory()->create(['is_admin' => true]));
});

it('deletes selected tags through the table bulk action', function () {
    $tags = Tag::factory()
        ->count(2)
        ->create();

    livewire(ListTags::class)
        ->selectTableRecords($tags)
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()
            ->bulk());

    expect(Tag::query()->whereKey($tags->pluck('id'))
        ->count())->toBe(0);
});

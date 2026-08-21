<?php

use App\Filament\Resources\Tags\Pages\ListTags;
use App\Models\Tag;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
});

it('deletes selected tags through the table bulk action', function () {
    $tags = collect([
        Tag::query()->create(['name' => 'First tag', 'slug' => 'first-tag']),
        Tag::query()->create(['name' => 'Second tag', 'slug' => 'second-tag']),
    ]);

    livewire(ListTags::class)
        ->selectTableRecords($tags)
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk());

    expect(Tag::query()->whereKey($tags->pluck('id'))->count())->toBe(0);
});

<?php

use App\Filament\Resources\Tags\Pages\EditTag;
use App\Models\Tag;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use function Pest\Livewire\livewire;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
});

it('deletes a tag through the authenticated resource', function () {
    $tag = Tag::query()->create([
        'name' => 'Tag delete coverage',
        'slug' => 'tag-delete-coverage',
    ]);

    livewire(EditTag::class, ['record' => $tag->getRouteKey()])
        ->callAction(DeleteAction::class);

    expect(Tag::query()->find($tag->getKey()))->toBeNull();
});

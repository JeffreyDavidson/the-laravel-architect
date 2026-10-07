<?php

use App\Filament\Resources\Tags\TagResource;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);
});

it('renders the tag create page for an authorized user', function () {
    get(TagResource::getUrl('create'))
        ->assertOk();
});

it('renders the tag edit page for an authorized user', function () {
    $tag = Tag::factory()->create();

    get(TagResource::getUrl('edit', ['record' => $tag]))
        ->assertOk();
});

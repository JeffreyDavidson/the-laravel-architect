<?php

use App\Filament\Resources\Tags\TagResource;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
});

it('renders the tag create page for an authorized user', function () {
    $this->get(TagResource::getUrl('create'))
        ->assertOk();
});

it('renders the tag edit page for an authorized user', function () {
    $tag = Tag::query()->create([
        'name' => 'Tag page coverage',
        'slug' => 'tag-page-coverage',
    ]);

    $this->get(TagResource::getUrl('edit', ['record' => $tag]))
        ->assertOk();
});

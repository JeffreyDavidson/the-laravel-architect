<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()->create(['is_admin' => true])));

dataset('slug locking types', ['post', 'project', 'episode', 'newsletter issue']);

it('lets an editor change the slug of a draft', function (string $type) {
    $record = PublishableFixtures::ready($type);

    livewire(PublishableFixtures::editPage($type), ['record' => $record->getRouteKey()])
        ->assertFormFieldEnabled('slug')
        ->fillForm(['slug' => 'a-new-slug'])
        ->call('save')
        ->assertHasNoFormErrors();

    $record->refresh();

    expect($record->getAttribute('slug'))
        ->toBe('a-new-slug');
})->with('slug locking types');

it('locks the slug field once content has been published', function (string $type) {
    $record = PublishableFixtures::ready($type);
    $record->publish();

    livewire(PublishableFixtures::editPage($type), ['record' => $record->getRouteKey()])
        ->assertFormFieldDisabled('slug');
})->with('slug locking types');

it('keeps the slug field locked after the content is unpublished', function (string $type) {
    $record = PublishableFixtures::ready($type);
    $record->publish();
    $record->unpublish();

    livewire(PublishableFixtures::editPage($type), ['record' => $record->getRouteKey()])
        ->assertFormFieldDisabled('slug');
})->with('slug locking types');

it('ignores a slug submitted for locked content', function (string $type) {
    $record = PublishableFixtures::ready($type);
    $record->publish();
    $slug = $record->getAttribute('slug');

    livewire(PublishableFixtures::editPage($type), ['record' => $record->getRouteKey()])
        ->fillForm(['slug' => 'hijacked-url'])
        ->call('save');

    $record->refresh();

    expect($record->getAttribute('slug'))
        ->toBe($slug);
})->with('slug locking types');

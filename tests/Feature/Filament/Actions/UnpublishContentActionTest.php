<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()->create(['is_admin' => true])));

it('returns published content to draft from its edit page and refreshes the form', function (string $type) {
    $record = PublishableFixtures::ready($type);
    $record->publish();

    livewire(PublishableFixtures::editPage($type), ['record' => $record->getRouteKey()])
        ->callAction('unpublish')
        ->assertNotified(Str::headline(class_basename($record)).' unpublished')
        ->assertSchemaStateSet(['status' => PublishStatus::Draft->value])
        ->assertActionHidden('unpublish')
        ->assertActionVisible('publish');

    $record->refresh();

    expect($record->getAttribute('status'))
        ->toBe(PublishStatus::Draft);
})->with([
    'post' => ['post'],
    'project' => ['project'],
    'episode' => ['episode'],
    'newsletter issue' => ['newsletter issue'],
]);

it('does not offer unpublishing for draft content', function () {
    // Arrange
    $record = PublishableFixtures::ready('episode');

    // Act
    $page = livewire(PublishableFixtures::editPage('episode'), ['record' => $record->getRouteKey()]);

    // Assert
    $page->assertActionHidden('unpublish');
});

it('offers unpublishing for live and scheduled content', function (int $days) {
    // Arrange
    $record = PublishableFixtures::ready('episode');
    $record->forceFill(['published_at' => now()->addDays($days)])
        ->save();
    $record->publish();

    // Act
    $page = livewire(PublishableFixtures::editPage('episode'), ['record' => $record->getRouteKey()]);

    // Assert
    $page->assertActionVisible('unpublish');
})->with([
    'published' => [-1],
    'scheduled' => [1],
]);

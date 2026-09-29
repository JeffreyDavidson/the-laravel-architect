<?php

use App\Enums\PublishStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()->create(['is_admin' => true])));

dataset('publishable types', ['post', 'project', 'episode', 'newsletter issue']);

it('publishes ready content from its edit page and refreshes the form', function (string $type) {
    $record = PublishableFixtures::ready($type);

    livewire(PublishableFixtures::editPage($type), ['record' => $record->getRouteKey()])
        ->callAction('publish')
        ->assertNotified(Str::headline(class_basename($record)).' published')
        ->assertSchemaStateSet(['status' => PublishStatus::Published->value])
        ->assertActionHidden('publish')
        ->assertActionVisible('unpublish');

    $record->refresh();

    expect($record->isPublished())
        ->toBeTrue();
})->with('publishable types');

it('refuses to publish content that is missing required details', function (string $type, string $field, string $issue) {
    $record = PublishableFixtures::ready($type, [$field => '']);

    livewire(PublishableFixtures::editPage($type), ['record' => $record->getRouteKey()])
        ->callAction('publish')
        ->assertNotified(Str::headline(class_basename($record)).' is not ready to publish');

    $record->refresh();

    expect($record->getAttribute('status'))
        ->toBe(PublishStatus::Draft)
        ->and($record->publishingIssues())
        ->toContain($issue);
})->with([
    'post without excerpt' => ['post', 'excerpt', 'Excerpt'],
    'project without case study' => ['project', 'content', 'Case study'],
    'episode without media' => ['episode', 'transistor_url', 'Episode media'],
    'newsletter issue without content' => ['newsletter issue', 'content', 'Content'],
]);

it('schedules content with a future publish date', function () {
    $record = PublishableFixtures::ready('post', ['published_at' => now()->addDay()]);

    livewire(PublishableFixtures::editPage('post'), ['record' => $record->getRouteKey()])
        ->callAction('publish')
        ->assertSchemaStateSet(['status' => PublishStatus::Scheduled->value]);

    $record->refresh();

    expect($record->isScheduled())
        ->toBeTrue();
});

it('offers publishing only for content that is neither live nor scheduled', function (?int $days, bool $visible) {
    $record = PublishableFixtures::ready('post');

    if ($days !== null) {
        $record->forceFill(['published_at' => now()->addDays($days)])
            ->save();
        $record->publish();
    }

    $page = livewire(PublishableFixtures::editPage('post'), ['record' => $record->getRouteKey()]);

    $visible
        ? $page->assertActionVisible('publish')
        : $page->assertActionHidden('publish');
})->with([
    'draft' => [null, true],
    'published' => [-1, false],
    'scheduled' => [1, false],
]);

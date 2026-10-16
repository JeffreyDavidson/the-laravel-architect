<?php

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use JeffreyDavidson\CreatorKit\Support\Time\DisplayTimezone;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\freezeSecond;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()->create(['is_admin' => true])));

dataset('publishable types', ['post', 'project', 'episode', 'newsletter issue']);

dataset('dated publishable types', ['post', 'episode', 'newsletter issue']);

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
        ->assertNotified(Notification::make()
            ->danger()
            ->title(Str::headline(class_basename($record)).' is not ready to publish')
            ->body($issue)
            ->persistent());

    $record->refresh();

    expect($record->getAttribute('status'))
        ->toBe(PublishStatus::Draft);
})->with([
    'post without excerpt' => ['post', 'excerpt', 'Excerpt'],
    'project without case study' => ['project', 'content', 'Case study'],
    'episode without media' => ['episode', 'transistor_url', 'Episode media'],
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

it('offers publishing for draft content', function () {
    // Arrange
    $record = PublishableFixtures::ready('post');

    // Act
    $page = livewire(PublishableFixtures::editPage('post'), ['record' => $record->getRouteKey()]);

    // Assert
    $page->assertActionVisible('publish');
});

it('does not offer publishing for content that is live or scheduled', function (int $days) {
    // Arrange
    $record = PublishableFixtures::ready('post');
    $record->forceFill(['published_at' => now()->addDays($days)])
        ->save();
    $record->publish();

    // Act
    $page = livewire(PublishableFixtures::editPage('post'), ['record' => $record->getRouteKey()]);

    // Assert
    $page->assertActionHidden('publish');
})->with([
    'published' => [-1],
    'scheduled' => [1],
]);

it('schedules content for a future publish date entered but not yet saved', function (string $type) {
    freezeSecond();
    $record = PublishableFixtures::ready($type);
    $publishAt = now()->addDays(3);

    livewire(PublishableFixtures::editPage($type), ['record' => $record->getRouteKey()])
        ->fillForm(['published_at' => $publishAt->copy()
            ->setTimezone(DisplayTimezone::name())
            ->format('Y-m-d H:i:s')])
        ->callAction('publish')
        ->assertHasNoFormErrors()
        ->assertSchemaStateSet(['status' => PublishStatus::Scheduled->value]);

    $record->refresh();
    $publishedAt = $record->publishedAt();

    expect($record->getAttribute('status'))
        ->toBe(PublishStatus::Scheduled)
        ->and($publishedAt?->equalTo($publishAt))
        ->toBeTrue();
})->with('dated publishable types');

it('publishes content immediately when the unsaved publish date is in the past', function (string $type) {
    // Arrange
    freezeSecond();
    $record = PublishableFixtures::ready($type, ['published_at' => now()->addWeek()]);
    $record->unpublish();
    $expectedPublishedAt = now()->subDays(2);

    // Act
    $component = livewire(PublishableFixtures::editPage($type), ['record' => $record->getRouteKey()])
        ->fillForm(['published_at' => $expectedPublishedAt->copy()
            ->setTimezone(DisplayTimezone::name())
            ->format('Y-m-d H:i:s')])
        ->callAction('publish');

    // Assert
    $component
        ->assertHasNoFormErrors()
        ->assertSchemaStateSet(['status' => PublishStatus::Published->value]);

    $record->refresh();
    $publishedAt = $record->publishedAt();

    expect($record->getAttribute('status'))
        ->toBe(PublishStatus::Published)
        ->and($publishedAt?->equalTo($expectedPublishedAt))
        ->toBeTrue();
})->with('dated publishable types');

it('publishes content immediately when the unsaved publish date is empty', function (string $type) {
    // Arrange
    freezeSecond();
    $record = PublishableFixtures::ready($type, ['published_at' => now()->addWeek()]);
    $record->unpublish();
    $expectedPublishedAt = now();

    // Act
    $component = livewire(PublishableFixtures::editPage($type), ['record' => $record->getRouteKey()])
        ->fillForm(['published_at' => null])
        ->callAction('publish');

    // Assert
    $component
        ->assertHasNoFormErrors()
        ->assertSchemaStateSet(['status' => PublishStatus::Published->value]);

    $record->refresh();
    $publishedAt = $record->publishedAt();

    expect($record->getAttribute('status'))
        ->toBe(PublishStatus::Published)
        ->and($publishedAt?->equalTo($expectedPublishedAt))
        ->toBeTrue();
})->with('dated publishable types');

it('checks readiness against details entered but not yet saved', function () {
    $record = PublishableFixtures::ready('post', ['excerpt' => '']);

    livewire(PublishableFixtures::editPage('post'), ['record' => $record->getRouteKey()])
        ->fillForm(['excerpt' => 'A summary typed before publishing.'])
        ->callAction('publish')
        ->assertNotified('Post published');

    $record->refresh();

    expect($record->isPublished())
        ->toBeTrue()
        ->and($record->getAttribute('excerpt'))
        ->toBe('A summary typed before publishing.');
});

it('publishes nothing when the form has validation errors', function (string $type, string $field) {
    $record = PublishableFixtures::ready($type);

    livewire(PublishableFixtures::editPage($type), ['record' => $record->getRouteKey()])
        ->fillForm([$field => ''])
        ->callAction('publish')
        ->assertHasErrors(["data.{$field}" => 'required']);

    $record->refresh();

    expect($record->getAttribute('status'))
        ->toBe(PublishStatus::Draft)
        ->and($record->getAttribute($field))
        ->not->toBe('');
})->with([
    'post without title' => ['post', 'title'],
    'project without title' => ['project', 'title'],
    'episode without title' => ['episode', 'title'],
    'newsletter issue without title' => ['newsletter issue', 'title'],
    'newsletter issue without content' => ['newsletter issue', 'content'],
]);

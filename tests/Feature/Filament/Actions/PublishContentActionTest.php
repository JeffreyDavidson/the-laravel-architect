<?php

use App\Enums\PublishStatus;
use App\Models\User;
use App\Support\DisplayTimezone;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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

it('publishes content immediately when the unsaved publish date is past or empty', function (string $type, ?int $daysAgo) {
    freezeSecond();
    $record = PublishableFixtures::ready($type, ['published_at' => now()->addWeek()]);
    $record->unpublish();
    $expectedPublishedAt = $daysAgo === null
        ? now()
        : now()->subDays($daysAgo);

    livewire(PublishableFixtures::editPage($type), ['record' => $record->getRouteKey()])
        ->fillForm(['published_at' => $daysAgo === null
            ? null
            : $expectedPublishedAt->copy()
                ->setTimezone(DisplayTimezone::name())
                ->format('Y-m-d H:i:s')])
        ->callAction('publish')
        ->assertHasNoFormErrors()
        ->assertSchemaStateSet(['status' => PublishStatus::Published->value]);

    $record->refresh();
    $publishedAt = $record->publishedAt();

    expect($record->getAttribute('status'))
        ->toBe(PublishStatus::Published)
        ->and($publishedAt?->equalTo($expectedPublishedAt))
        ->toBeTrue();
})
    ->with('dated publishable types')
    ->with([
        'past date' => [2],
        'empty date' => [null],
    ]);

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

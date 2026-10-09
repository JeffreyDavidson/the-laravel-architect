<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\travel;

pest()->use(RefreshDatabase::class);

dataset('slug locking types', ['post', 'project', 'episode', 'newsletter issue']);

it('leaves the slug of a draft unlocked', function (string $type) {
    $record = PublishableFixtures::ready($type);

    expect($record->isSlugLocked())
        ->toBeFalse()
        ->and($record->getAttribute('slug_locked_at'))
        ->toBeNull();
})->with('slug locking types');

it('locks the slug when content is first published', function (string $type) {
    $record = PublishableFixtures::ready($type);

    $record->publish();
    $record->refresh();

    expect($record->isSlugLocked())
        ->toBeTrue()
        ->and($record->getAttribute('slug_locked_at'))
        ->not->toBeNull();
})->with('slug locking types');

it('keeps the slug locked after the content is unpublished', function (string $type) {
    $record = PublishableFixtures::ready($type);
    $record->publish();

    $record->unpublish();
    $record->refresh();

    expect($record->publishStatus())
        ->toBe(PublishStatus::Draft)
        ->and($record->isSlugLocked())
        ->toBeTrue()
        ->and($record->getAttribute('slug_locked_at'))
        ->not->toBeNull();
})->with('slug locking types');

it('locks the slug of scheduled content', function (string $type) {
    $record = PublishableFixtures::ready($type, ['published_at' => now()->addDay()]);

    $record->publish();

    expect($record->isScheduled())
        ->toBeTrue()
        ->and($record->isSlugLocked())
        ->toBeTrue();
})->with(['post', 'episode', 'newsletter issue']);

it('locks content that is saved as published without going through publish()', function (string $type, array $attributes) {
    /** @var array<string, mixed> $attributes */
    $record = PublishableFixtures::ready($type, $attributes);

    expect($record->getAttribute('slug_locked_at'))
        ->not->toBeNull()
        ->and($record->isSlugLocked())
        ->toBeTrue();
})->with([
    'post' => ['post', ['status' => PublishStatus::Published, 'published_at' => '2026-01-01 00:00:00']],
    'project' => ['project', ['status' => PublishStatus::Published]],
    'episode' => ['episode', ['status' => PublishStatus::Published, 'published_at' => '2026-01-01 00:00:00']],
    'newsletter issue' => ['newsletter issue', ['status' => PublishStatus::Published, 'published_at' => '2026-01-01 00:00:00']],
]);

it('does not move the lock once it is set', function () {
    $record = PublishableFixtures::ready('post');
    $record->publish();
    $lockedAt = $record->getAttribute('slug_locked_at');

    travel(2)->days();
    $record->unpublish();
    $record->publish();
    $record->refresh();

    expect($record->getAttribute('slug_locked_at'))
        ->toEqual($lockedAt);
});

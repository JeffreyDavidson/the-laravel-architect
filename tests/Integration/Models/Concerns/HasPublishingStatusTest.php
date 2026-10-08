<?php

use App\Enums\PublishStatus;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PublishableFixtures;

pest()->use(RefreshDatabase::class);

dataset('dated publishable types', ['post', 'episode', 'newsletter issue']);

it('publishes immediately when no publish date is set', function (string $type) {
    $record = PublishableFixtures::ready($type);

    $record->publish();

    $record->refresh();
    expect($record->isPublished())
        ->toBeTrue()
        ->and($record->isScheduled())
        ->toBeFalse()
        ->and($record->getAttribute('status'))
        ->toBe(PublishStatus::Published)
        ->and($record->getAttribute('published_at'))
        ->not->toBeNull();
})->with('dated publishable types');

it('keeps an existing past publish date', function (string $type) {
    $publishedAt = now()
        ->subWeek()
        ->startOfSecond();
    $record = PublishableFixtures::ready($type, ['published_at' => $publishedAt]);

    $record->publish();

    $record->refresh();
    $savedPublishedAt = $record->getAttribute('published_at');

    expect($savedPublishedAt instanceof CarbonInterface && $savedPublishedAt->equalTo($publishedAt))
        ->toBeTrue();
})->with('dated publishable types');

it('schedules content whose publish date is in the future', function (string $type) {
    $record = PublishableFixtures::ready($type, ['published_at' => now()->addDay()]);

    $record->publish();

    $record->refresh();
    expect($record->isPublished())
        ->toBeFalse()
        ->and($record->isScheduled())
        ->toBeTrue()
        ->and($record->getAttribute('status'))
        ->toBe(PublishStatus::Scheduled);
})->with('dated publishable types');

it('publishes a project by status alone', function () {
    $project = PublishableFixtures::ready('project');

    $project->publish();

    $project->refresh();

    expect($project->isPublished())
        ->toBeTrue()
        ->and($project->isScheduled())
        ->toBeFalse();
});

it('returns published and scheduled content to draft while keeping its date and slug', function (string $type, ?int $days) {
    $publishedAt = $days === null
        ? null
        : now()
            ->addDays($days)
            ->startOfSecond();
    $record = PublishableFixtures::ready($type, ['published_at' => $publishedAt]);
    $record->publish();
    $slug = $record->getAttribute('slug');
    $publishedAt = $record->getAttribute('published_at');

    $record->unpublish();

    $record->refresh();
    expect($record->getAttribute('status'))
        ->toBe(PublishStatus::Draft)
        ->and($record->isPublished())
        ->toBeFalse()
        ->and($record->isScheduled())
        ->toBeFalse()
        ->and($record->getAttribute('slug'))
        ->toBe($slug)
        ->and($record->getAttribute('published_at') == $publishedAt)
        ->toBeTrue();
})->with([
    'published post' => ['post', -1],
    'scheduled episode' => ['episode', 1],
    'published project' => ['project', null],
]);

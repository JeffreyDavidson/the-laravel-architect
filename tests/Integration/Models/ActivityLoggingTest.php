<?php

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

pest()->use(RefreshDatabase::class);

function activityCountFor(Model $subject): int
{
    return Activity::query()
        ->forSubject($subject)
        ->count();
}

function latestActivityFor(Model $subject): Activity
{
    return Activity::query()
        ->forSubject($subject)
        ->latest('id')
        ->firstOrFail();
}

it('does not record post activity when only long-form content changes', function () {
    // Arrange
    $post = Post::factory()->create();
    $post->refresh();
    $initialActivityCount = activityCountFor($post);

    // Act
    $post->update(['content' => 'Updated content.']);

    // Assert
    expect(activityCountFor($post))
        ->toBe($initialActivityCount);
});

it('records operational post changes without recording long-form content', function () {
    // Arrange
    $post = Post::factory()->create();
    $post->refresh();

    // Act
    $post->update(['title' => 'Updated activity logging']);

    // Assert
    expect(latestActivityFor($post)->attribute_changes?->get('attributes'))
        ->toHaveKey('title', 'Updated activity logging')
        ->not
        ->toHaveKeys(['content', 'excerpt', 'review_notes']);
});

it('does not record newsletter issue activity when only long-form content changes', function () {
    // Arrange
    $issue = NewsletterIssue::factory()->create();
    $issue->refresh();
    $initialActivityCount = activityCountFor($issue);

    // Act
    $issue->update(['content' => 'Updated content.', 'excerpt' => 'Updated excerpt.']);

    // Assert
    expect(activityCountFor($issue))
        ->toBe($initialActivityCount);
});

it('records operational newsletter issue changes without recording long-form content', function () {
    // Arrange
    $issue = NewsletterIssue::factory()->create();
    $issue->refresh();

    // Act
    $issue->update(['title' => 'Updated activity logging']);

    // Assert
    expect(latestActivityFor($issue)->attribute_changes?->get('attributes'))
        ->toHaveKey('title', 'Updated activity logging')
        ->not
        ->toHaveKeys(['content', 'excerpt']);
});

it('does not record synchronized video statistics', function () {
    $video = Video::factory()->create();
    $video->refresh();
    $initialActivityCount = activityCountFor($video);

    $video->update([
        'description' => 'Updated description.',
        'view_count' => 100,
        'like_count' => 10,
        'comment_count' => 1,
        'synced_at' => now(),
    ]);

    expect(activityCountFor($video))
        ->toBe($initialActivityCount);
});

it('records content changes in the application log', function (Model $subject) {
    expect(latestActivityFor($subject)->log_name)
        ->toBe('application');
})->with([
    'post' => fn (): Post => Post::factory()->create(),
    'episode' => fn (): Episode => Episode::factory()->create(),
    'podcast' => fn (): Podcast => Podcast::factory()->create(),
    'project' => fn (): Project => Project::factory()->create(),
    'newsletter issue' => fn (): NewsletterIssue => NewsletterIssue::factory()->create(),
    'video' => fn (): Video => Video::factory()->create(),
]);

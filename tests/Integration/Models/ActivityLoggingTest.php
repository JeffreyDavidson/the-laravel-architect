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

it('records operational post changes without recording long-form content', function () {
    $post = Post::factory()->create();
    $post->refresh();
    $initialActivityCount = activityCountFor($post);

    $post->update(['content' => 'Updated content.']);

    expect(activityCountFor($post))
        ->toBe($initialActivityCount);

    $post->update(['title' => 'Updated activity logging']);

    expect(latestActivityFor($post)->attribute_changes?->get('attributes'))
        ->toHaveKey('title', 'Updated activity logging')
        ->not
        ->toHaveKeys(['content', 'excerpt', 'review_notes']);
});

it('records operational newsletter issue changes without recording long-form content', function () {
    $issue = NewsletterIssue::factory()->create();
    $issue->refresh();
    $initialActivityCount = activityCountFor($issue);

    $issue->update(['content' => 'Updated content.', 'excerpt' => 'Updated excerpt.']);

    expect(activityCountFor($issue))
        ->toBe($initialActivityCount);

    $issue->update(['title' => 'Updated activity logging']);

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

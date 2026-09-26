<?php

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
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
    $post = Post::query()->create([
        'title' => 'Activity logging',
        'content' => 'Original content.',
        'user_id' => User::factory()
            ->create()
            ->getKey(),
    ]);
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
    $issue = NewsletterIssue::query()->create([
        'title' => 'Activity logging',
        'content' => 'Original content.',
    ]);
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
    $video = Video::query()->create([
        'youtube_id' => 'video-id',
        'title' => 'Laravel Video',
        'slug' => 'laravel-video',
    ]);
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
    'post' => fn (): Post => Post::query()->create([
        'title' => 'Logged post',
        'content' => 'Content.',
        'user_id' => User::factory()
            ->create()
            ->getKey(),
    ]),
    'episode' => fn (): Episode => Episode::query()->create([
        'podcast_id' => Podcast::query()
            ->create([
                'name' => 'Logged podcast',
                'slug' => 'logged-podcast',
                'description' => 'Description.',
            ])
            ->getKey(),
        'title' => 'Logged episode',
        'slug' => 'logged-episode',
        'description' => 'Description.',
    ]),
    'podcast' => fn (): Podcast => Podcast::query()->create([
        'name' => 'Logged podcast',
        'slug' => 'logged-podcast',
        'description' => 'Description.',
    ]),
    'project' => fn (): Project => Project::query()->create([
        'title' => 'Logged project',
        'slug' => 'logged-project',
        'description' => 'Description.',
    ]),
    'newsletter issue' => fn (): NewsletterIssue => NewsletterIssue::query()->create([
        'title' => 'Logged issue',
        'content' => 'Content.',
    ]),
    'video' => fn (): Video => Video::query()->create([
        'youtube_id' => 'logged-video',
        'title' => 'Logged video',
        'slug' => 'logged-video',
    ]),
]);

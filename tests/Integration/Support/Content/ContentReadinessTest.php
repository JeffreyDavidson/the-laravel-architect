<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use App\Models\Video;
use App\Support\Content\ContentReadiness;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('reports actionable missing details for every supported content type', function () {
    $user = User::factory()->create();
    $records = [
        Post::query()->create([
            'title' => 'Incomplete post',
            'slug' => 'incomplete-post',
            'content' => 'Content.',
            'user_id' => $user->id,
        ]),
        Project::query()->create([
            'title' => 'Incomplete project',
            'slug' => 'incomplete-project',
            'description' => 'Description.',
        ]),
        Podcast::query()->create([
            'name' => 'Incomplete podcast',
            'slug' => 'incomplete-podcast',
            'description' => 'Description.',
        ]),
        Episode::query()->create([
            'title' => 'Incomplete episode',
            'slug' => 'incomplete-episode',
            'description' => 'Description.',
        ]),
        NewsletterIssue::query()->create([
            'title' => 'Incomplete issue',
            'slug' => 'incomplete-issue',
            'content' => 'Content.',
        ]),
        Video::query()->create([
            'youtube_id' => 'incomplete-video',
            'title' => 'Incomplete video',
            'slug' => 'incomplete-video',
        ]),
    ];

    foreach ($records as $record) {
        $readiness = new ContentReadiness($record);

        expect($readiness->isReady())->toBeFalse()
            ->and($readiness->label())->toBe('Needs attention')
            ->and($readiness->progress())->toContain(' complete')
            ->and($readiness->missingSummary())->toStartWith('Missing: ');
    }
});

it('reports complete public details for each content type that supports readiness checks', function () {
    $user = User::factory()->create();
    $category = Category::query()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    $tag = Tag::query()->create(['name' => 'Featured', 'slug' => 'featured']);

    $post = Post::query()->create([
        'title' => 'Complete post',
        'slug' => 'complete-post',
        'excerpt' => 'Summary.',
        'content' => 'Content.',
        'featured_image_path' => 'posts/complete.webp',
        'category_id' => $category->id,
        'user_id' => $user->id,
        'status' => PublishStatus::Draft,
    ]);
    $post->attachTag($tag);

    $project = Project::query()->create([
        'title' => 'Complete project',
        'slug' => 'complete-project',
        'description' => 'Description.',
        'content' => 'Case study.',
        'featured_image_path' => 'projects/complete.webp',
        'url' => 'https://example.com',
        'tech_stack' => ['Laravel'],
    ]);
    $project->attachTag($tag);

    $podcast = Podcast::query()->create([
        'name' => 'Complete podcast',
        'slug' => 'complete-podcast',
        'description' => 'Description.',
        'long_description' => 'About.',
        'cover_image_path' => 'podcasts/complete.webp',
        'rss_url' => 'https://example.com/feed.xml',
    ]);

    $episode = Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Complete episode',
        'slug' => 'complete-episode',
        'description' => 'Description.',
        'show_notes' => 'Show notes.',
        'audio_url' => 'https://example.com/audio.mp3',
        'featured_image_path' => 'episodes/complete.webp',
    ]);
    $episode->attachTag($tag);

    $issue = NewsletterIssue::query()->create([
        'title' => 'Complete issue',
        'slug' => 'complete-issue',
        'excerpt' => 'Summary.',
        'content' => 'Content.',
    ]);

    $video = Video::query()->create([
        'youtube_id' => 'complete-video',
        'title' => 'Complete video',
        'slug' => 'complete-video',
        'description' => 'Description.',
        'thumbnail_url' => 'https://example.com/thumb.jpg',
        'duration' => '10:00',
        'synced_at' => now(),
    ]);

    foreach ([$post, $project, $podcast, $episode, $issue, $video] as $record) {
        expect(new ContentReadiness($record)->isReady())->toBeTrue();
    }
});

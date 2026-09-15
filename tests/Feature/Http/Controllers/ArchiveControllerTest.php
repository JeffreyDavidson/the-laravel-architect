<?php

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('browses published public content in one chronological archive', function () {
    $author = User::factory()->create();
    $post = Post::query()->create([
        'title' => 'Archive writing',
        'slug' => 'archive-writing',
        'excerpt' => 'A note for the archive.',
        'content' => 'Published content.',
        'user_id' => $author->id,
        'status' => PublishStatus::Published,
        'published_at' => now()->subDays(2),
    ]);
    $project = Project::query()->create([
        'title' => 'Archive project',
        'slug' => 'archive-project',
        'description' => 'A project in the archive.',
        'status' => PublishStatus::Published,
    ]);
    $podcast = Podcast::query()->create([
        'name' => 'Archive podcast',
        'slug' => 'archive-podcast',
        'description' => 'A podcast in the archive.',
        'is_active' => true,
    ]);
    $episode = Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Archive episode',
        'slug' => 'archive-episode',
        'description' => 'An episode in the archive.',
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);
    $issue = NewsletterIssue::query()->create([
        'title' => 'Archive newsletter',
        'slug' => 'archive-newsletter',
        'excerpt' => 'A newsletter in the archive.',
        'content' => 'Published issue.',
        'status' => PublishStatus::Published,
        'published_at' => now()->subDays(3),
    ]);
    $video = Video::query()->create([
        'youtube_id' => 'archive-video',
        'title' => 'Archive video',
        'slug' => 'archive-video',
        'description' => 'A video in the archive.',
        'published_at' => now()->subDays(4),
    ]);
    Post::query()->create([
        'title' => 'Draft excluded',
        'slug' => 'draft-excluded',
        'content' => 'Private content.',
        'user_id' => $author->id,
        'status' => PublishStatus::Draft,
    ]);

    $response = $this->get(route('archive.index'))
        ->assertOk()
        ->assertSee('Everything worth revisiting.')
        ->assertSee($post->title)
        ->assertSee($project->title)
        ->assertSee($podcast->name)
        ->assertSee($episode->title)
        ->assertSee($issue->title)
        ->assertSee($video->title)
        ->assertDontSee('Draft excluded')
        ->assertSeeHtml('name="type"')
        ->assertSeeHtml('name="year"')
        ->assertSeeHtml('"@type":"CollectionPage"')
        ->assertSeeHtml('"@type":"ItemList"');

    $content = $response->getContent();
    if (! is_string($content)) {
        throw new RuntimeException('Expected archive response content.');
    }

    $episodePosition = strpos($content, (string) $episode->title);
    $postPosition = strpos($content, (string) $post->title);

    if ($episodePosition === false || $postPosition === false) {
        throw new RuntimeException('Expected archive records in response content.');
    }

    expect($episodePosition)->toBeLessThan($postPosition)
        ->and($content)->toContain(route('podcast.episode', [$podcast, $episode]));
});

it('filters the archive by content type and year', function () {
    $author = User::factory()->create();
    $post = Post::query()->create([
        'title' => 'Current archive article',
        'slug' => 'current-archive-article',
        'content' => 'Published content.',
        'user_id' => $author->id,
        'status' => PublishStatus::Published,
        'published_at' => '2026-03-01 12:00:00',
    ]);
    Project::query()->create([
        'title' => 'Current archive project',
        'slug' => 'current-archive-project',
        'description' => 'A project.',
        'status' => PublishStatus::Published,
    ]);

    $this->get(route('archive.index', ['type' => 'writing', 'year' => 2026]))
        ->assertOk()
        ->assertSee($post->title)
        ->assertDontSee('Current archive project')
        ->assertSee('Showing 1–1 of 1 items.');
});

it('rejects invalid archive filters', function (array $filters) {
    $this->get(route('archive.index', $filters))->assertNotFound();
})->with([
    'unknown type' => [['type' => 'unknown']],
    'invalid year' => [['year' => 1999]],
    'invalid page' => [['page' => 0]],
]);

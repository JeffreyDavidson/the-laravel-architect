<?php

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;
use function Pest\Laravel\travelTo;

pest()->use(RefreshDatabase::class);

it('browses published public content in one chronological archive', function () {
    $post = Post::factory()->published()
        ->create([
            'title' => 'Archive writing',
            'published_at' => now()->subDays(2),
        ]);
    $project = Project::factory()->published()
        ->create(['title' => 'Archive project']);
    $podcast = Podcast::factory()->create(['name' => 'Archive podcast']);
    $episode = Episode::factory()->for($podcast)
        ->published()
        ->create(['title' => 'Archive episode']);
    $issue = NewsletterIssue::factory()->published()
        ->create([
            'title' => 'Archive newsletter',
            'published_at' => now()->subDays(3),
        ]);
    $video = Video::factory()->create([
        'title' => 'Archive video',
        'published_at' => now()->subDays(4),
    ]);
    Post::factory()->create(['title' => 'Draft excluded']);

    $response = get(route('archive.index'))
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
        ->and($content)
        ->toContain(route('podcast.episode', [$podcast, $episode]));
});

it('filters the archive by content type and year', function () {
    $post = Post::factory()->published()
        ->create(['published_at' => '2026-03-01 12:00:00']);
    Project::factory()->published()
        ->create(['title' => 'Current archive project']);

    get(route('archive.index', ['type' => 'writing', 'year' => 2026]))
        ->assertOk()
        ->assertSee($post->title)
        ->assertDontSee('Current archive project')
        ->assertSee('Showing 1–1 of 1 items.');
});

it('dates archive entries in the display timezone', function () {
    config(['app.display_timezone' => 'America/New_York']);
    travelTo('2026-10-10 12:00:00');
    Post::factory()->published()
        ->create(['published_at' => '2026-10-06 01:00:00']);

    get(route('archive.index', ['type' => 'writing']))
        ->assertOk()
        ->assertSeeHtml('datetime="2026-10-05"')
        ->assertSee('October 5, 2026')
        ->assertDontSee('October 6, 2026');
});

it('leaves out an episode whose podcast is in the trash', function () {
    $podcast = Podcast::factory()->create();
    $episode = Episode::factory()->for($podcast)
        ->published()
        ->create(['title' => 'Orphaned episode']);
    $podcast->delete();
    $episode->restore();

    get(route('archive.index'))
        ->assertOk()
        ->assertDontSee('Orphaned episode');
});

it('returns not found for an out-of-range archive page', function () {
    Post::factory()->published()
        ->create();

    get(route('archive.index', ['page' => 2]))
        ->assertNotFound();
});

it('rejects invalid archive filters', function (array $filters) {
    get(route('archive.index', $filters))
        ->assertNotFound();
})->with([
    'unknown type' => [['type' => 'unknown']],
    'invalid year' => [['year' => 1999]],
    'invalid page' => [['page' => 0]],
]);

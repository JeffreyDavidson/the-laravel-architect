<?php

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;
use function Pest\Laravel\travelTo;

pest()->use(RefreshDatabase::class);

it('renders episode show notes and transcript as safe Markdown', function () {
    $podcast = Podcast::query()->create([
        'name' => 'Architecture Sessions',
        'slug' => 'architecture-sessions',
        'description' => 'Conversations about Laravel architecture.',
        'is_active' => true,
    ]);
    $episode = Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Maintaining Clear Boundaries',
        'slug' => 'maintaining-clear-boundaries',
        'description' => 'A practical architecture discussion.',
        'show_notes' => <<<'MARKDOWN'
### Topics Covered

- Keep **domain boundaries** explicit.
- Read the [Laravel documentation](https://laravel.com/docs).

<script>alert('unsafe')</script>

[Unsafe link](javascript:alert('unsafe'))
MARKDOWN,
        'transcript' => <<<'MARKDOWN'
### Transcript

Welcome to the **episode**.

<script>alert('unsafe')</script>

[Unsafe link](javascript:alert('unsafe'))
MARKDOWN,
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('podcast.episode', [$podcast, $episode]))
        ->assertOk()
        ->assertSeeHtml('<h3>Topics Covered</h3>')
        ->assertSeeHtml('<strong>domain boundaries</strong>')
        ->assertSeeHtml('<a href="https://laravel.com/docs">Laravel documentation</a>')
        ->assertSee('Read transcript')
        ->assertSee('data-transcript')
        ->assertSee('data-transcript-search')
        ->assertSee('data-transcript-content')
        ->assertSeeHtml('<h3>Transcript</h3>')
        ->assertSeeHtml('<strong>episode</strong>')
        ->assertDontSeeHtml("<script>alert('unsafe')</script>")
        ->assertDontSeeHtml('<a href="javascript:');
});

it('does not render an empty transcript section', function () {
    $podcast = Podcast::query()->create([
        'name' => 'Architecture Sessions',
        'slug' => 'architecture-sessions',
        'description' => 'Conversations about Laravel architecture.',
        'is_active' => true,
    ]);
    $episode = Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Maintaining Clear Boundaries',
        'slug' => 'maintaining-clear-boundaries',
        'description' => 'A practical architecture discussion.',
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('podcast.episode', [$podcast, $episode]))
        ->assertOk()
        ->assertDontSee('Read transcript');
});

it('shows an episode publish date in the display timezone', function () {
    config(['app.display_timezone' => 'America/New_York']);
    travelTo('2026-10-10 12:00:00');
    $podcast = Podcast::query()->create([
        'name' => 'Architecture Sessions',
        'slug' => 'architecture-sessions',
        'description' => 'Conversations about Laravel architecture.',
        'is_active' => true,
    ]);
    $episode = Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Evening Episode',
        'slug' => 'evening-episode',
        'description' => 'Published in the evening.',
        'status' => PublishStatus::Published,
        'published_at' => '2026-10-06 01:00:00',
    ]);

    get(route('podcast.episode', [$podcast, $episode]))
        ->assertOk()
        ->assertSee('October 05, 2026')
        ->assertSee('Oct 05, 2026')
        ->assertSeeHtml('datetime="2026-10-05"')
        ->assertDontSeeHtml('datetime="2026-10-06"')
        ->assertSeeHtml('"datePublished":"2026-10-06T01:00:00+00:00"');
});

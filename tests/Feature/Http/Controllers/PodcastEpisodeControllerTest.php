<?php

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders episode show notes as safe Markdown', function () {
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
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('podcast.episode', [$podcast, $episode]))
        ->assertOk()
        ->assertSee('<h3>Topics Covered</h3>', false)
        ->assertSee('<strong>domain boundaries</strong>', false)
        ->assertSee('<a href="https://laravel.com/docs">Laravel documentation</a>', false)
        ->assertDontSee("<script>alert('unsafe')</script>", false)
        ->assertDontSee('javascript:', false);
});

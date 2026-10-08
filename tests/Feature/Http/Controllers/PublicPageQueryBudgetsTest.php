<?php

use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\Video;
use App\Services\ScaleTestContentWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\expectsDatabaseQueryCount;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

/**
 * Seed a content set large enough that a page whose query count grows with
 * its content (an N+1) exceeds its budget.
 */
function seedQueryBudgetContent(): void
{
    app(ScaleTestContentWorkflow::class)
        ->seed();

    $posts = Post::query()
        ->where('slug', 'like', 'scale-test-post-%')
        ->orderBy('id')
        ->limit(30)
        ->get();

    foreach ($posts as $post) {
        $post->attachTags(['Budget Tag', "Budget Tag {$post->id}"]);
    }

    foreach (range(1, 30) as $number) {
        NewsletterIssue::factory()->published()
            ->create([
                'title' => "Budget Issue {$number}",
                'slug' => "budget-issue-{$number}",
                'published_at' => now()->subDays($number),
            ]);
        Video::factory()->create([
            'published_at' => now()->subDays($number),
        ]);
    }
}

it('keeps each public page within its query budget as content grows', function (string $url, int $budget) {
    seedQueryBudgetContent();

    expectsDatabaseQueryCount($budget);

    get($url)
        ->assertOk();
})->with([
    'home' => [fn (): string => route('home'), 12],
    'blog index' => [fn (): string => route('blog.index'), 7],
    'post' => [fn (): string => route('blog.show', 'scale-test-post-001'), 10],
    'category' => [fn (): string => route('blog.category', 'scale-test-category'), 7],
    'tag' => [fn (): string => route('blog.tag', 'budget-tag'), 7],
    'podcast index' => [fn (): string => route('podcasts.index'), 3],
    'podcast' => [fn (): string => route('podcasts.show', 'scale-test-podcast'), 6],
    'episode' => [fn (): string => route('podcasts.episode', ['scale-test-podcast', 'scale-test-episode-001']), 9],
    'projects index' => [fn (): string => route('projects.index'), 4],
    'project' => [fn (): string => route('projects.show', 'scale-test-project-001'), 7],
    'newsletter index' => [fn (): string => route('newsletter.index'), 4],
    'newsletter issue' => [fn (): string => route('newsletter.issue', 'budget-issue-1'), 4],
    'archive' => [fn (): string => route('archive.index'), 5],
    'search' => [fn (): string => route('search', ['q' => 'scale']), 13],
    'about' => [fn (): string => route('about'), 2],
    'contact' => [fn (): string => route('contact.create'), 4],
    'newsletter confirmed' => [fn (): string => route('newsletter.confirmed'), 4],
]);

<?php

use App\Enums\PublishStatus;
use App\Models\NewsletterIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;
use function Pest\Laravel\travelTo;

pest()->use(RefreshDatabase::class);

it('gives each newsletter archive page its own canonical URL', function () {
    foreach (range(1, 13) as $number) {
        NewsletterIssue::query()->create([
            'title' => "Issue {$number}", 'slug' => "issue-{$number}", 'content' => 'Content',
            'status' => PublishStatus::Published, 'published_at' => now()->subDays($number),
        ]);
    }
    $url = route('newsletter.index', ['page' => 2]);

    $this->get($url)
        ->assertSeeHtml('<link rel="canonical" href="'.$url.'">')
        ->assertSeeHtml('<title>Newsletter Archive — Page 2 — Jeffrey Davidson</title>');
});

it('lists published newsletter issues and excludes drafts', function () {
    $published = NewsletterIssue::query()->create([
        'title' => 'Building Better Boundaries',
        'slug' => 'building-better-boundaries',
        'excerpt' => 'A practical note about application boundaries.',
        'content' => 'Published content.',
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);
    NewsletterIssue::query()->create([
        'title' => 'Draft Issue',
        'slug' => 'draft-issue',
        'content' => 'Not public.',
        'status' => PublishStatus::Draft,
    ]);

    $this->get(route('newsletter.index'))
        ->assertOk()
        ->assertSee($published->title)
        ->assertSee('Newsletter Archive')
        ->assertDontSee('Draft Issue');
});

it('renders a published issue as safe Markdown', function () {
    $issue = NewsletterIssue::query()->create([
        'title' => 'A Safe Issue',
        'slug' => 'a-safe-issue',
        'excerpt' => 'A safe issue excerpt.',
        'content' => <<<'MARKDOWN'
## Practical Notes

Use **small changes**.

<script>alert('unsafe')</script>

[Unsafe link](javascript:alert('unsafe'))
MARKDOWN,
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('newsletter.issue', $issue))
        ->assertOk()
        ->assertSee($issue->title)
        ->assertSeeHtml('<h2>Practical Notes</h2>')
        ->assertSeeHtml('<strong>small changes</strong>')
        ->assertDontSeeHtml("<script>alert('unsafe')</script>")
        ->assertDontSeeHtml('<a href="javascript:');
});

it('does not expose draft newsletter issues', function () {
    $issue = NewsletterIssue::query()->create([
        'title' => 'Private Draft',
        'slug' => 'private-draft',
        'content' => 'Not public.',
        'status' => PublishStatus::Draft,
    ]);

    $this->get(route('newsletter.issue', $issue))
        ->assertNotFound();
});

it('shows a newsletter issue publish date in the display timezone', function () {
    config(['app.display_timezone' => 'America/New_York']);
    travelTo('2026-10-10 12:00:00');
    $issue = NewsletterIssue::query()->create([
        'title' => 'Evening Issue',
        'slug' => 'evening-issue',
        'content' => 'Published in the evening.',
        'status' => PublishStatus::Published,
        'published_at' => '2026-10-06 01:00:00',
    ]);

    get(route('newsletter.index'))
        ->assertOk()
        ->assertSeeHtml('datetime="2026-10-05"')
        ->assertSee('October 5, 2026');

    get(route('newsletter.issue', $issue))
        ->assertOk()
        ->assertSeeHtml('datetime="2026-10-05"')
        ->assertSeeText('Published October 5, 2026');
});

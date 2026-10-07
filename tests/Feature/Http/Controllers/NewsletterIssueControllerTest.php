<?php

use App\Models\NewsletterIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;
use function Pest\Laravel\travelTo;

pest()->use(RefreshDatabase::class);

it('gives each newsletter archive page its own canonical URL', function () {
    NewsletterIssue::factory()->count(13)
        ->published()
        ->create();
    $url = route('newsletter.index', ['page' => 2]);

    get($url)
        ->assertSeeHtml('<link rel="canonical" href="'.$url.'">')
        ->assertSeeHtml('<title>Newsletter Archive — Page 2 — Jeffrey Davidson</title>');
});

it('returns not found for an out-of-range newsletter archive page', function () {
    NewsletterIssue::factory()->published()
        ->create();

    get(route('newsletter.index', ['page' => 2]))
        ->assertNotFound();
});

it('lists published newsletter issues and excludes drafts', function () {
    $published = NewsletterIssue::factory()->published()
        ->create();
    NewsletterIssue::factory()->create([
        'title' => 'Draft Issue',
    ]);

    get(route('newsletter.index'))
        ->assertOk()
        ->assertSee($published->title)
        ->assertSee('Newsletter Archive')
        ->assertDontSee('Draft Issue');
});

it('renders a published issue as safe Markdown', function () {
    $issue = NewsletterIssue::factory()->published()
        ->create([
            'content' => <<<'MARKDOWN'
## Practical Notes

Use **small changes**.

<script>alert('unsafe')</script>

[Unsafe link](javascript:alert('unsafe'))
MARKDOWN,
        ]);

    get(route('newsletter.issue', $issue))
        ->assertOk()
        ->assertSee($issue->title)
        ->assertSeeHtml('<h2>Practical Notes</h2>')
        ->assertSeeHtml('<strong>small changes</strong>')
        ->assertDontSeeHtml("<script>alert('unsafe')</script>")
        ->assertDontSeeHtml('<a href="javascript:');
});

it('does not expose draft newsletter issues', function () {
    $issue = NewsletterIssue::factory()->create();

    get(route('newsletter.issue', $issue))
        ->assertNotFound();
});

it('shows a newsletter issue publish date in the display timezone', function () {
    config(['app.display_timezone' => 'America/New_York']);
    travelTo('2026-10-10 12:00:00');
    $issue = NewsletterIssue::factory()->published()
        ->create([
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

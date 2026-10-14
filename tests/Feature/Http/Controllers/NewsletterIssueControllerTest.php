<?php

use App\Enums\NewsletterTemplate;
use App\Models\NewsletterIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;
use function Pest\Laravel\travelTo;

pest()->use(RefreshDatabase::class);

describe('index', function () {
    it('gives each newsletter archive page its own canonical URL', function () {
        NewsletterIssue::factory()->count(13)
            ->published()
            ->create();
        $url = route('newsletter.index', ['page' => 2]);

        $response = get($url);

        $response
            ->assertSeeHtml('<link rel="canonical" href="'.$url.'">')
            ->assertSeeHtml('<title>Newsletter Archive — Page 2 — Jeffrey Davidson</title>');
    });

    it('returns not found for an out-of-range newsletter archive page', function () {
        NewsletterIssue::factory()->published()
            ->create();

        $response = get(route('newsletter.index', ['page' => 2]));

        $response
            ->assertNotFound();
    });

    it('lists published newsletter issues and excludes drafts', function () {
        $published = NewsletterIssue::factory()->published()
            ->create();
        NewsletterIssue::factory()->create([
            'title' => 'Draft Issue',
        ]);

        $response = get(route('newsletter.index'));

        $response
            ->assertOk()
            ->assertSee($published->title)
            ->assertSee('Newsletter Archive')
            ->assertDontSee('Draft Issue');
    });

    it('shows a newsletter issue publish date in the display timezone on newsletter.index', function () {
        config(['app.display_timezone' => 'America/New_York']);
        travelTo('2026-10-10 12:00:00');
        $issue = NewsletterIssue::factory()->published()
            ->create(['published_at' => '2026-10-06 01:00:00']);
        $response = get(route('newsletter.index'));
        $response->assertOk()
            ->assertSeeHtml('datetime="2026-10-05"')
            ->assertSee('October 5, 2026');
    });
});

describe('show', function () {
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

        $response = get(route('newsletter.issue', $issue));

        $response
            ->assertOk()
            ->assertSee($issue->title)
            ->assertSeeHtml('<h2>Practical Notes</h2>')
            ->assertSeeHtml('<strong>small changes</strong>')
            ->assertDontSeeHtml("<script>alert('unsafe')</script>")
            ->assertDontSeeHtml('<a href="javascript:');
    });

    it('does not expose draft newsletter issues', function () {
        $issue = NewsletterIssue::factory()->create();

        $response = get(route('newsletter.issue', $issue));

        $response
            ->assertNotFound();
    });

    it('includes the editorial introduction and primary article link in the web version', function () {
        $issue = NewsletterIssue::factory()
            ->published()
            ->create([
                'email_template' => NewsletterTemplate::NewArticle,
                'email_deck' => 'An introduction shared with the email.',
                'email_options' => ['article_url' => 'https://example.test/blog/example', 'button_label' => 'Read the full example'],
            ]);

        \Pest\Laravel\get(route('newsletter.issue', $issue))
            ->assertOk()
            ->assertSee('An introduction shared with the email.')
            ->assertSee('https://example.test/blog/example')
            ->assertSee('Read the full example');
    });

    it('shows a newsletter issue publish date in the display timezone on newsletter.issue', function () {
        config(['app.display_timezone' => 'America/New_York']);
        travelTo('2026-10-10 12:00:00');
        $issue = NewsletterIssue::factory()->published()
            ->create(['published_at' => '2026-10-06 01:00:00']);
        $response = get(route('newsletter.issue', $issue));
        $response->assertOk()
            ->assertSeeHtml('datetime="2026-10-05"')
            ->assertSeeText('Published October 5, 2026');
    });
});

<?php

use App\Enums\PublishStatus;
use App\Models\NewsletterIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

<?php

use App\Models\NewsletterIssue;
use App\Presenters\NewsletterIssuePresenter;

covers(NewsletterIssuePresenter::class);

function newsletterIssuePresenter(string $content): NewsletterIssuePresenter
{
    return NewsletterIssuePresenter::from(NewsletterIssue::factory()->make(['content' => $content]));
}

it('strips raw html and unsafe links from the email body', function () {
    $html = newsletterIssuePresenter("<script>alert('x')</script>\n\n[Click](javascript:alert(1))")
        ->emailBodyHtml();

    expect($html)
        ->not->toContain('<script>', 'javascript:');
});

it('makes relative links and images absolute in the email body', function () {
    config()->set('app.url', 'https://example.test');
    $presenter = newsletterIssuePresenter(
        "[Post](/blog/x) and [Rel](blog/y)\n\n![Pic](/storage/pic.png)\n\n[Paged](/blog?page=2&a=1)",
    );

    $html = $presenter->emailBodyHtml();
    $text = $presenter->emailBodyText();

    expect($html)
        ->toContain(
            'href="https://example.test/blog/x"',
            'href="https://example.test/blog/y"',
            'src="https://example.test/storage/pic.png"',
            'href="https://example.test/blog?page=2&amp;a=1"',
        )
        ->not->toContain('href="/blog')
        ->and($text)
        ->toContain('[Post](https://example.test/blog/x)', '![Pic](https://example.test/storage/pic.png)')
        ->not->toContain('](/');
});

it('leaves absolute, mailto, tel and anchor urls alone in the email body', function () {
    config()->set('app.url', 'https://example.test');
    $markdown = '[A](https://other.test/a) [B](mailto:me@example.com) [C](tel:+15555550100) [D](#section) [E](//cdn.test/e)';
    $presenter = newsletterIssuePresenter($markdown);

    $html = $presenter->emailBodyHtml();
    $text = $presenter->emailBodyText();

    expect($html)
        ->toContain(
            'href="https://other.test/a"',
            'href="mailto:me@example.com"',
            'href="tel:+15555550100"',
            'href="#section"',
            'href="//cdn.test/e"',
        )
        ->and($text)
        ->toBe($markdown);
});

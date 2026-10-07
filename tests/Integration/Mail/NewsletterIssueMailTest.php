<?php

use App\Mail\NewsletterIssueMail;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

function publishedNewsletterIssue(string $content = 'Hello **readers**.'): NewsletterIssue
{
    return NewsletterIssue::factory()
        ->published()
        ->create([
            'title' => 'Issue One & Beyond',
            'slug' => 'issue-one',
            'content' => $content,
        ]);
}

it('renders the issue as html and plain text with its links', function () {
    $unsubscribeUrl = 'https://example.test/newsletter/unsubscribe/1?signature=abc&x=1';

    $mail = new NewsletterIssueMail(publishedNewsletterIssue(), $unsubscribeUrl);

    $mail->assertHasSubject('Issue One & Beyond')
        ->assertSeeInHtml('<strong>readers</strong>', false)
        ->assertSeeInHtml(route('newsletter.issue', 'issue-one'))
        ->assertSeeInHtml($unsubscribeUrl)
        ->assertSeeInText('Hello **readers**.')
        ->assertSeeInText(route('newsletter.issue', 'issue-one'))
        ->assertSeeInText($unsubscribeUrl);
});

it('strips raw html and unsafe links from the issue content', function () {
    $issue = publishedNewsletterIssue("<script>alert('x')</script>\n\n[Click](javascript:alert(1))");

    $mail = new NewsletterIssueMail($issue, 'https://example.test/unsubscribe');

    $mail->assertDontSeeInHtml('<script>', false)
        ->assertDontSeeInHtml('javascript:', false);
});

it('offers one-click unsubscribe headers to mail clients', function () {
    $unsubscribeUrl = 'https://example.test/newsletter/unsubscribe/1?signature=abc';

    $headers = new NewsletterIssueMail(publishedNewsletterIssue(), $unsubscribeUrl)
        ->headers();

    expect($headers->text)
        ->toBe([
            'List-Unsubscribe' => "<{$unsubscribeUrl}>",
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
});

it('marks test emails and omits unsubscribe headers', function () {
    $mail = new NewsletterIssueMail(publishedNewsletterIssue());

    $headers = $mail->headers();

    $mail->assertSeeInHtml('This is a test email')
        ->assertSeeInText('This is a test email');
    expect($headers->text)
        ->toBeEmpty();
});

it('gives each delivery a stable provider idempotency key', function () {
    $issue = publishedNewsletterIssue();
    [$delivery, $otherDelivery] = NewsletterDelivery::factory()
        ->count(2)
        ->create()
        ->all();
    $unsubscribeUrl = 'https://example.test/newsletter/unsubscribe/1?signature=abc';

    $headers = new NewsletterIssueMail($issue, $unsubscribeUrl, $delivery)
        ->headers()
        ->text;
    $retryHeaders = new NewsletterIssueMail($issue, $unsubscribeUrl, $delivery->refresh())
        ->headers()
        ->text;
    $otherHeaders = new NewsletterIssueMail($issue, $unsubscribeUrl, $otherDelivery)
        ->headers()
        ->text;

    expect($headers)
        ->toHaveKey('Resend-Idempotency-Key')
        ->toHaveKey('List-Unsubscribe', "<{$unsubscribeUrl}>")
        ->toBe($retryHeaders)
        ->and($headers['Resend-Idempotency-Key'])
        ->toStartWith('tla-newsletter-delivery-')
        ->not->toBe($otherHeaders['Resend-Idempotency-Key'] ?? null);
});

it('uses the themed email layout with a preheader from the excerpt', function () {
    $issue = publishedNewsletterIssue();
    $issue->update(['excerpt' => 'A short teaser for the inbox.']);

    $mail = new NewsletterIssueMail($issue, 'https://example.test/unsubscribe');

    $mail->assertSeeInHtml('<meta name="color-scheme" content="light dark" />', false)
        ->assertSeeInHtml('prefers-color-scheme: dark', false)
        ->assertSeeInHtml('A short teaser for the inbox.');
});

it('falls back to the title for the preheader without an excerpt', function () {
    $mail = new NewsletterIssueMail(publishedNewsletterIssue(), 'https://example.test/unsubscribe');

    $mail->assertSeeInHtml('display: none', false)
        ->assertSeeInHtml('Issue One &amp; Beyond', false);
});

it('fixes the card width for Outlook with a conditional wrapper', function () {
    $mail = new NewsletterIssueMail(publishedNewsletterIssue(), 'https://example.test/unsubscribe');

    $mail->assertSeeInHtml('<!--[if mso]>', false)
        ->assertSeeInHtml('width="600"', false)
        ->assertSeeInHtml('<![endif]-->', false);
});

it('makes relative links and images absolute in html and text', function () {
    config()->set('app.url', 'https://example.test');
    $issue = publishedNewsletterIssue(
        "[Post](/blog/x) and [Rel](blog/y)\n\n![Pic](/storage/pic.png)\n\n[Paged](/blog?page=2&a=1)",
    );

    $mail = new NewsletterIssueMail($issue, 'https://example.test/unsubscribe');

    $mail->assertSeeInHtml('href="https://example.test/blog/x"', false)
        ->assertSeeInHtml('href="https://example.test/blog/y"', false)
        ->assertSeeInHtml('src="https://example.test/storage/pic.png"', false)
        ->assertSeeInHtml('href="https://example.test/blog?page=2&amp;a=1"', false)
        ->assertDontSeeInHtml('href="/blog', false)
        ->assertSeeInText('[Post](https://example.test/blog/x)')
        ->assertSeeInText('![Pic](https://example.test/storage/pic.png)')
        ->assertDontSeeInText('](/');
});

it('leaves absolute, mailto, tel and anchor urls alone', function () {
    config()->set('app.url', 'https://example.test');
    $markdown = '[A](https://other.test/a) [B](mailto:me@example.com) [C](tel:+15555550100) [D](#section) [E](//cdn.test/e)';

    $mail = new NewsletterIssueMail(publishedNewsletterIssue($markdown), 'https://example.test/unsubscribe');

    $mail->assertSeeInHtml('href="https://other.test/a"', false)
        ->assertSeeInHtml('href="mailto:me@example.com"', false)
        ->assertSeeInHtml('href="tel:+15555550100"', false)
        ->assertSeeInHtml('href="#section"', false)
        ->assertSeeInHtml('href="//cdn.test/e"', false)
        ->assertSeeInText($markdown);
});

it('shows the unsubscribe link in the footer only for subscriber emails', function () {
    $issue = publishedNewsletterIssue();
    $unsubscribeUrl = 'https://example.test/newsletter/unsubscribe/1?signature=abc';

    new NewsletterIssueMail($issue, $unsubscribeUrl)
        ->assertSeeInHtml('>Unsubscribe</a>', false)
        ->assertSeeInHtml($unsubscribeUrl, false);
    new NewsletterIssueMail($issue)
        ->assertDontSeeInHtml('>Unsubscribe</a>', false)
        ->assertSeeInHtml('This is a test email');
});

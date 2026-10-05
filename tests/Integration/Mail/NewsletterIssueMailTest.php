<?php

use App\Enums\PublishStatus;
use App\Mail\NewsletterIssueMail;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

function publishedNewsletterIssue(string $content = 'Hello **readers**.'): NewsletterIssue
{
    return NewsletterIssue::query()->create([
        'title' => 'Issue One & Beyond',
        'slug' => 'issue-one',
        'content' => $content,
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
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

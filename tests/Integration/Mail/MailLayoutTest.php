<?php

use App\Enums\PublishStatus;
use App\Mail\ConfirmNewsletterSubscription;
use App\Mail\NewsletterIssueMail;
use App\Models\NewsletterIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailable;

pest()->use(RefreshDatabase::class);

dataset('layout mailables', [
    'newsletter confirmation' => fn (): Mailable => new ConfirmNewsletterSubscription('https://example.test/confirm?signature=abc'),
    'newsletter issue' => fn (): Mailable => new NewsletterIssueMail(
        NewsletterIssue::query()->create([
            'title' => 'Issue One',
            'slug' => 'issue-one',
            'content' => 'Hello readers.',
            'status' => PublishStatus::Published,
            'published_at' => now()->subDay(),
        ]),
        'https://example.test/unsubscribe',
    ),
]);

it('balances its table markup for clients that ignore Outlook conditional comments', function (Mailable $mail) {
    $html = (string) preg_replace('/<!--\[if mso\]>.*?<!\[endif\]-->/s', '', $mail->render());

    foreach (['table', 'tr', 'td'] as $tag) {
        expect(preg_match_all("/<{$tag}[\\s>]/", $html))
            ->toBe(substr_count($html, "</{$tag}>"));
    }
})->with('layout mailables');

it('opens and closes the Outlook fixed-width wrapper inside conditional comments', function (Mailable $mail) {
    preg_match_all('/<!--\[if mso\]>(.*?)<!\[endif\]-->/s', $mail->render(), $matches);
    $blocks = array_map(fn (string $block): string => (string) preg_replace('/\s+/', '', $block), $matches[1]);

    expect($blocks)
        ->toHaveCount(2)
        ->and($blocks[0])
        ->toStartWith('<tablerole="presentation"align="center"width="600"')
        ->toEndWith('<tr><td>')
        ->and($blocks[1])
        ->toBe('</td></tr></table>');
})->with('layout mailables');

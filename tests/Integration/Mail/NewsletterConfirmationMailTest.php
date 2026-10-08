<?php

use App\Mail\NewsletterConfirmationMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

pest()->use(RefreshDatabase::class);

it('preserves signed query parameters in its plain text confirmation link', function () {
    $url = URL::temporarySignedRoute('newsletter.confirm', now()->addDay(), [
        'subscriber' => 1,
        'token' => 'confirmation-token',
    ]);
    $mail = new NewsletterConfirmationMail($url);

    $mail->assertSeeInText($url);
});

it('renders a themed html confirmation with the link, a button and the expiry', function () {
    $url = URL::temporarySignedRoute('newsletter.confirm', now()->addDay(), [
        'subscriber' => 1,
        'token' => 'confirmation-token',
    ]);
    $mail = new NewsletterConfirmationMail($url);

    $mail->assertHasSubject('Confirm your subscription')
        ->assertSeeInHtml('Confirm your subscription')
        ->assertSeeInHtml('Confirm subscription')
        ->assertSeeInHtml($url)
        ->assertSeeInHtml('expires in 24 hours')
        ->assertSeeInHtml('The Laravel Architect')
        ->assertSeeInHtml(url('/images/elephant-companion-180.png'))
        ->assertSeeInHtml(route('privacy'))
        ->assertSeeInText('expires in 24 hours');
});

it('keeps the html confirmation free of scripts and external stylesheets', function () {
    $mail = new NewsletterConfirmationMail('https://example.test/confirm?expires=1&signature=abc');

    $mail->assertDontSeeInHtml('<script', false)
        ->assertDontSeeInHtml('<link', false)
        ->assertDontSeeInHtml('@import', false);
});

it('escapes the confirmation link in the html and leaves it intact in the text', function () {
    $url = 'https://example.test/confirm?expires=1&signature=abc"onmouseover="x';
    $mail = new NewsletterConfirmationMail($url);

    $mail->assertDontSeeInHtml('signature=abc"onmouseover', false)
        ->assertSeeInText($url);
});

it('encrypts sensitive content in the queued mail payload', function () {
    $mail = new NewsletterConfirmationMail('https://example.test/confirm?expires=123&signature=private-token');

    Mail::to('recipient@example.test')->queue($mail->onConnection('database'));

    $payload = DB::table('jobs')->sole()
        ->payload;
    if (! is_string($payload)) {
        throw new RuntimeException('Expected a JSON queue payload.');
    }
    $command = data_get(json_decode($payload, true, flags: JSON_THROW_ON_ERROR), 'data.command');
    if (! is_string($command)) {
        throw new RuntimeException('Expected a serialized queued command.');
    }

    expect($command)->not->toContain('private-token')
        ->and(Crypt::decrypt($command))
        ->toContain('private-token');
});

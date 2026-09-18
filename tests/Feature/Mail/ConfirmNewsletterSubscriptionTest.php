<?php

use App\Mail\ConfirmNewsletterSubscription;
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
    $mail = new ConfirmNewsletterSubscription($url);

    $mail->assertSeeInText($url);
});

it('encrypts sensitive content in the queued mail payload', function () {
    $mail = new ConfirmNewsletterSubscription('https://example.test/confirm?expires=123&signature=private-token');

    Mail::to('recipient@example.test')->queue($mail->onConnection('database'));

    $payload = DB::table('jobs')->sole()->payload;
    if (! is_string($payload)) {
        throw new RuntimeException('Expected a JSON queue payload.');
    }
    $command = data_get(json_decode($payload, true, flags: JSON_THROW_ON_ERROR), 'data.command');
    if (! is_string($command)) {
        throw new RuntimeException('Expected a serialized queued command.');
    }

    expect($command)->not->toContain('private-token')
        ->and(Crypt::decrypt($command))->toContain('private-token');
});

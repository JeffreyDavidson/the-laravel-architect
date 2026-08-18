<?php

use App\Mail\ContactMessageConfirmation;
use App\Mail\ContactMessageReceived;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    RateLimiter::clear('contact-form:127.0.0.1');
    Mail::fake();
});

it('silently accepts honeypot submissions without sending mail', function () {
    $this->post(route('contact.submit'), [
        'name' => 'Spam Bot',
        'email' => 'spam@example.com',
        'type' => 'freelance',
        'budget' => 'small',
        'message' => 'This should not send.',
        'website' => 'filled-by-bot',
    ])->assertSessionHas('success');

    Mail::assertNothingQueued();
});

it('queues both contact messages after a valid submission', function () {
    $this->post(route('contact.submit'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'type' => 'consulting',
        'message' => 'Can you help with an audit?',
    ])->assertSessionHas('success');

    Mail::assertQueued(
        ContactMessageReceived::class,
        fn (ContactMessageReceived $mail): bool => $mail->senderEmail === 'jane@example.com'
            && str_contains($mail->render(), 'Can you help with an audit?'),
    );
    Mail::assertQueued(
        ContactMessageConfirmation::class,
        fn (ContactMessageConfirmation $mail): bool => $mail->senderName === 'Jane Doe'
            && str_contains($mail->render(), 'Here\'s a copy of your message.'),
    );
    expect(RateLimiter::attempts('contact-form:127.0.0.1'))->toBe(1);
});

it('does not count invalid submissions against the rate limit', function () {
    $this->post(route('contact.submit'), [
        'email' => 'not-an-email',
    ])->assertSessionHasErrors(['name', 'email', 'type', 'message']);

    expect(RateLimiter::attempts('contact-form:127.0.0.1'))->toBe(0);
    Mail::assertNothingQueued();
});

it('rate limits repeated contact submissions by ip address', function () {
    RateLimiter::hit('contact-form:127.0.0.1', 3600);
    RateLimiter::hit('contact-form:127.0.0.1', 3600);
    RateLimiter::hit('contact-form:127.0.0.1', 3600);

    $this->post(route('contact.submit'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'type' => 'consulting',
        'message' => 'Can you help with an audit?',
    ])->assertSessionHasErrors('message');

    Mail::assertNothingQueued();
});

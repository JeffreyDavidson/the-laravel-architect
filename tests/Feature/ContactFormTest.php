<?php

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

    Mail::assertNothingSent();
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

    Mail::assertNothingSent();
});

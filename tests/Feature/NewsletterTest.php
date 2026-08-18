<?php

use App\Mail\ConfirmNewsletterSubscription;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
});

it('creates an unverified subscriber and sends a confirmation message', function () {
    $this->post(route('newsletter.subscribe'), ['email' => 'Reader@Example.com'])
        ->assertRedirect()
        ->assertSessionHas('newsletter_success', 'Check your email to confirm your subscription.');

    $subscriber = Subscriber::query()->sole();

    expect($subscriber->email)->toBe('reader@example.com')
        ->and($subscriber->verified_at)->toBeNull()
        ->and($subscriber->verification_token)->not->toBeNull();

    Mail::assertSent(ConfirmNewsletterSubscription::class, 1);
});

it('confirms a subscriber with a valid signed link', function () {
    $token = 'valid-confirmation-token';
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
        'verification_token' => hash('sha256', $token),
    ]);

    $url = URL::temporarySignedRoute(
        'newsletter.confirm',
        now()->addHour(),
        ['subscriber' => $subscriber, 'token' => $token],
    );

    $this->get($url)
        ->assertRedirect(route('home'))
        ->assertSessionHas('newsletter_success');

    expect($subscriber->refresh()->verified_at)->not->toBeNull()
        ->and($subscriber->verification_token)->toBeNull();
});

it('does not disclose whether an email is already subscribed', function () {
    Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
        'verified_at' => now(),
    ]);

    $this->post(route('newsletter.subscribe'), ['email' => 'reader@example.com'])
        ->assertSessionHas('newsletter_success', 'Check your email to confirm your subscription.');

    Mail::assertNothingSent();
});

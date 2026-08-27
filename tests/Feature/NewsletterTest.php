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

    Mail::assertQueued(ConfirmNewsletterSubscription::class, 1);
});

it('silently accepts newsletter honeypot submissions without subscribing', function () {
    $this->post(route('newsletter.subscribe'), [
        'website' => 'filled-by-bot',
    ])->assertSessionHas('newsletter_success');

    expect(Subscriber::query()->count())->toBe(0);
    Mail::assertNothingQueued();
});

it('shows an explicit confirmation step without changing subscriber state', function () {
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
        ->assertOk()
        ->assertSee('Confirm your subscription')
        ->assertSee($subscriber->email)
        ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

    expect($subscriber->refresh()->verified_at)->toBeNull()
        ->and($subscriber->verification_token)->not->toBeNull();
});

it('confirms a subscriber with an explicit post to a valid signed link', function () {
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

    $this->post($url)
        ->assertRedirect(route('home'))
        ->assertSessionHas('newsletter_success');

    expect($subscriber->refresh()->verified_at)->not->toBeNull()
        ->and($subscriber->verification_token)->toBeNull();
});

it('rejects unsigned newsletter state changes', function () {
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
        'verification_token' => hash('sha256', 'token'),
    ]);

    $this->post(route('newsletter.confirm.store', [$subscriber, 'token']))
        ->assertForbidden();

    expect($subscriber->refresh()->verified_at)->toBeNull();
});

it('shows an unsubscribe step without changing subscriber state', function () {
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
        'verified_at' => now(),
    ]);

    $this->get($subscriber->unsubscribeUrl())
        ->assertOk()
        ->assertSee('Unsubscribe from the newsletter')
        ->assertSee($subscriber->email)
        ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

    expect($subscriber->refresh()->unsubscribed_at)->toBeNull();
});

it('unsubscribes with an explicit post to a valid signed link', function () {
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
        'verified_at' => now(),
    ]);

    $this->post($subscriber->unsubscribeUrl())
        ->assertRedirect(route('home'))
        ->assertSessionHas('newsletter_success', 'You have been unsubscribed.');

    expect($subscriber->refresh()->unsubscribed_at)->not->toBeNull();
});

it('does not disclose whether an email is already subscribed', function () {
    Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
        'verified_at' => now(),
    ]);

    $this->post(route('newsletter.subscribe'), ['email' => 'reader@example.com'])
        ->assertSessionHas('newsletter_success', 'Check your email to confirm your subscription.');

    Mail::assertNothingQueued();
});

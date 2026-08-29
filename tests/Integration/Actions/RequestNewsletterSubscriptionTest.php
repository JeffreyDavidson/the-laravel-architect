<?php

use App\Actions\RequestNewsletterSubscription;
use App\Mail\ConfirmNewsletterSubscription;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
});

it('starts a pending newsletter subscription and queues its confirmation', function () {
    app(RequestNewsletterSubscription::class)('Reader@Example.com');

    $subscriber = Subscriber::query()->sole();

    expect($subscriber->email)->toBe('reader@example.com')
        ->and($subscriber->subscribed_at)->not->toBeNull()
        ->and($subscriber->verified_at)->toBeNull()
        ->and($subscriber->unsubscribed_at)->toBeNull()
        ->and($subscriber->verification_token_hash)->not->toBeNull();

    Mail::assertQueued(
        ConfirmNewsletterSubscription::class,
        fn (ConfirmNewsletterSubscription $mail): bool => $mail->hasTo('reader@example.com')
            && URL::hasValidSignature(Request::create($mail->confirmationUrl)),
    );
});

it('does not restart an active verified subscription', function () {
    $subscribedAt = now()->subMonth()->startOfSecond();
    $verifiedAt = now()->subMonth()->addMinute()->startOfSecond();
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => $subscribedAt,
        'verified_at' => $verifiedAt,
    ]);

    app(RequestNewsletterSubscription::class)('Reader@Example.com');

    expect($subscriber->refresh()->subscribed_at?->equalTo($subscribedAt))->toBeTrue()
        ->and($subscriber->verified_at?->equalTo($verifiedAt))->toBeTrue()
        ->and($subscriber->verification_token_hash)->toBeNull();

    Mail::assertNothingQueued();
});

it('restarts confirmation for an unsubscribed reader', function () {
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now()->subMonth(),
        'verified_at' => now()->subMonth()->addMinute(),
        'unsubscribed_at' => now()->subWeek(),
    ]);

    app(RequestNewsletterSubscription::class)('reader@example.com');

    expect($subscriber->refresh()->subscribed_at?->isToday())->toBeTrue()
        ->and($subscriber->verified_at)->toBeNull()
        ->and($subscriber->unsubscribed_at)->toBeNull()
        ->and($subscriber->verification_token_hash)->not->toBeNull();

    Mail::assertQueued(ConfirmNewsletterSubscription::class, 1);
});

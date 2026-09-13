<?php

use App\Actions\RequestNewsletterSubscription;
use App\Mail\ConfirmNewsletterSubscription;
use App\Models\Subscriber;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Mail\PendingMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use JMac\Testing\Double;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
});

it('starts a pending newsletter subscription and queues its confirmation', function () {
    app(RequestNewsletterSubscription::class)
        ->handle('Reader@Example.com');

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

    app(RequestNewsletterSubscription::class)
        ->handle('Reader@Example.com');

    $subscriber->refresh();

    expect(Date::parse($subscriber->subscribed_at)->equalTo($subscribedAt))->toBeTrue()
        ->and(Date::parse($subscriber->verified_at)->equalTo($verifiedAt))->toBeTrue()
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

    app(RequestNewsletterSubscription::class)
        ->handle('reader@example.com');

    $subscriber->refresh();

    expect(Date::parse($subscriber->subscribed_at)->isToday())->toBeTrue()
        ->and($subscriber->verified_at)->toBeNull()
        ->and($subscriber->unsubscribed_at)->toBeNull()
        ->and($subscriber->verification_token_hash)->not->toBeNull();

    Mail::assertQueued(ConfirmNewsletterSubscription::class, 1);
});

it('preserves a pending confirmation link during the email cooldown', function () {
    $action = app(RequestNewsletterSubscription::class);
    $action->handle('reader@example.com');

    $subscriber = Subscriber::query()->sole();
    $tokenHash = $subscriber->verification_token_hash;
    $subscribedAt = $subscriber->subscribed_at;

    $action->handle('READER@example.com');

    expect($subscriber->refresh()->verification_token_hash)->toBe($tokenHash)
        ->and(Date::parse($subscriber->subscribed_at)->equalTo($subscribedAt))->toBeTrue();

    Mail::assertQueued(ConfirmNewsletterSubscription::class, 1);
});

it('allows a pending confirmation to be requested again after the cooldown expires', function () {
    $action = app(RequestNewsletterSubscription::class);
    $action->handle('reader@example.com');
    $tokenHash = Subscriber::query()->sole()->verification_token_hash;

    $this->travel(901)->seconds();
    $action->handle('reader@example.com');

    expect(Subscriber::query()->sole()->verification_token_hash)->not->toBe($tokenHash);

    Mail::assertQueued(ConfirmNewsletterSubscription::class, 2);
});

it('does not retain the cooldown when queueing the confirmation fails', function () {
    $pendingMail = Double::for(PendingMail::class);
    $pendingMail->expects('queue')->throws(new RuntimeException('mail transport unavailable'));

    $mailer = Double::for(Mailer::class);
    $mailer->expects('to')->with('reader@example.com')->returns($pendingMail);
    app()->instance(Mailer::class, $mailer);

    expect(fn () => app(RequestNewsletterSubscription::class)->handle('reader@example.com'))
        ->toThrow(RuntimeException::class, 'mail transport unavailable');

    $cooldownKey = 'newsletter.confirmation.cooldown.'.hash('sha256', 'reader@example.com');

    expect(Cache::has($cooldownKey))->toBeFalse();
});

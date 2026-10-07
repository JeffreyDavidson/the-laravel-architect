<?php

use App\Actions\SuppressSubscriber;
use App\Enums\SuppressionReason;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\freezeTime;
use function Pest\Laravel\travel;

pest()->use(RefreshDatabase::class);

covers(SuppressSubscriber::class);

it('unsubscribes an active reader and records why', function () {
    $reader = Subscriber::factory()->create(['email' => 'reader@example.com']);
    $reader->verification_token_hash = hash('sha256', 'token');
    $reader->save();

    $found = app(SuppressSubscriber::class)->handle(' Reader@Example.com ', SuppressionReason::Bounced);

    $reader->refresh();

    expect($found)
        ->toBeTrue()
        ->and($reader->unsubscribed_at)
        ->not->toBeNull()
        ->and($reader->suppressed_at)
        ->not->toBeNull()
        ->and($reader->suppression_reason)
        ->toBe(SuppressionReason::Bounced)
        ->and($reader->verification_token_hash)
        ->toBeNull()
        ->and($reader->isActive())
        ->toBeFalse();
});

it('keeps the original unsubscribe date', function () {
    freezeTime();
    $unsubscribedAt = now()->subDays(3)
        ->startOfSecond();
    $reader = Subscriber::factory()->create(['unsubscribed_at' => $unsubscribedAt]);

    app(SuppressSubscriber::class)->handle($reader->email, SuppressionReason::Complained);

    $reader->refresh();

    expect($reader->unsubscribed_at?->equalTo($unsubscribedAt))
        ->toBeTrue();
});

it('stops the confirmation of a pending sign-up', function () {
    $reader = Subscriber::factory()
        ->pending()
        ->create();

    app(SuppressSubscriber::class)->handle($reader->email, SuppressionReason::Bounced);

    $reader->refresh();

    expect($reader->isSuppressed())
        ->toBeTrue();
});

it('keeps the first reason and date when suppressed twice', function () {
    freezeTime();
    $reader = Subscriber::factory()->create();
    $action = app(SuppressSubscriber::class);

    $action->handle($reader->email, SuppressionReason::Bounced);
    travel(2)
        ->days();
    $found = $action->handle($reader->email, SuppressionReason::Complained);

    $reader->refresh();

    expect($found)
        ->toBeTrue()
        ->and($reader->suppression_reason)
        ->toBe(SuppressionReason::Bounced)
        ->and($reader->suppressed_at?->isSameDay(now()->subDays(2)))
        ->toBeTrue();
});

it('changes nothing for an unknown address', function () {
    $reader = Subscriber::factory()->create();

    $found = app(SuppressSubscriber::class)->handle('stranger@example.com', SuppressionReason::Bounced);

    $reader->refresh();

    expect($found)
        ->toBeFalse()
        ->and($reader->isActive())
        ->toBeTrue();
});

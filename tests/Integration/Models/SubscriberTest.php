<?php

use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('only counts verified subscriptions that have not unsubscribed as active', function () {
    $active = Subscriber::query()->create(['email' => 'active@example.test', 'verified_at' => now()]);
    $pending = Subscriber::query()->create(['email' => 'pending@example.test']);
    $unsubscribed = Subscriber::query()->create(['email' => 'gone@example.test', 'verified_at' => now(), 'unsubscribed_at' => now()]);

    expect(Subscriber::query()->active()
        ->pluck('id')
        ->all())->toBe([$active->id])
        ->and($active->isActive())
        ->toBeTrue()
        ->and($pending->isActive())
        ->toBeFalse()
        ->and($unsubscribed->isActive())
        ->toBeFalse();
});

it('does not mass assign its verification token hash', function () {
    $subscriber = new Subscriber;

    $subscriber->fill([
        'email' => 'reader@example.com',
        'verification_token_hash' => hash('sha256', 'secret-token'),
    ]);

    expect($subscriber->email)->toBe('reader@example.com')
        ->and($subscriber->getAttributes())
        ->not->toHaveKey('verification_token_hash');
});

it('hides its verification token hash from serialization', function () {
    $subscriber = new Subscriber;
    $subscriber->email = 'reader@example.com';
    $subscriber->verification_token_hash = hash('sha256', 'secret-token');

    expect($subscriber->toArray())
        ->toHaveKey('email', 'reader@example.com')
        ->not->toHaveKey('verification_token_hash');
});

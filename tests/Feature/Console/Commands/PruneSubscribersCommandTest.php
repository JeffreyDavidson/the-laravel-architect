<?php

use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

pest()->use(RefreshDatabase::class);

it('prunes stale subscribers', function (Carbon $subscribedAt, ?Carbon $verifiedAt, ?Carbon $unsubscribedAt) {
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => $subscribedAt,
        'verified_at' => $verifiedAt,
        'unsubscribed_at' => $unsubscribedAt,
    ]);

    $this->artisanCommand('model:prune', ['--model' => Subscriber::class])
        ->assertSuccessful();

    $this->assertModelMissing($subscriber);
})->with([
    'unconfirmed past the grace period' => [
        fn (): Carbon => now()->subDays(8),
        null,
        null,
    ],
    'unsubscribed past the grace period' => [
        fn (): Carbon => now()->subYear(),
        fn (): Carbon => now()->subYear(),
        fn (): Carbon => now()->subDays(31),
    ],
]);

it('keeps current subscribers', function (Carbon $subscribedAt, ?Carbon $verifiedAt, ?Carbon $unsubscribedAt) {
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => $subscribedAt,
        'verified_at' => $verifiedAt,
        'unsubscribed_at' => $unsubscribedAt,
    ]);

    $this->artisanCommand('model:prune', ['--model' => Subscriber::class])
        ->assertSuccessful();

    $this->assertModelExists($subscriber);
})->with([
    'unconfirmed within the grace period' => [
        fn (): Carbon => now()->subDays(6),
        null,
        null,
    ],
    'unsubscribed within the grace period' => [
        fn (): Carbon => now()->subYear(),
        fn (): Carbon => now()->subYear(),
        fn (): Carbon => now()->subDays(29),
    ],
    'active' => [
        fn (): Carbon => now()->subYear(),
        fn (): Carbon => now()->subYear(),
        null,
    ],
]);

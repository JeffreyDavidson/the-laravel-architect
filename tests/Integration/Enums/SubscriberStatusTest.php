<?php

use App\Enums\SubscriberStatus;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

covers(SubscriberStatus::class);

it('follows the confirmation and unsubscribe dates', function (?string $verifiedAt, ?string $unsubscribedAt, SubscriberStatus $expected) {
    $subscriber = new Subscriber(['verified_at' => $verifiedAt, 'unsubscribed_at' => $unsubscribedAt]);

    $status = SubscriberStatus::for($subscriber);

    expect($status)
        ->toBe($expected);
})->with([
    'confirmed' => ['2026-09-01', null, SubscriberStatus::Active],
    'never confirmed' => [null, null, SubscriberStatus::Pending],
    'confirmed then unsubscribed' => ['2026-09-01', '2026-09-10', SubscriberStatus::Unsubscribed],
    'unsubscribed before confirming' => [null, '2026-09-10', SubscriberStatus::Unsubscribed],
]);

it('reports a suppressed subscriber as suppressed even though it is also unsubscribed', function () {
    $subscriber = new Subscriber(['verified_at' => '2026-09-01', 'unsubscribed_at' => '2026-09-10', 'suppressed_at' => '2026-09-10']);

    $status = SubscriberStatus::for($subscriber);

    expect($status)
        ->toBe(SubscriberStatus::Suppressed);
});

it('narrows a query to each status', function (SubscriberStatus $status, string $visible) {
    $subscribers = [
        'active' => Subscriber::factory()
            ->create(),
        'pending' => Subscriber::factory()
            ->pending()
            ->create(),
        'unsubscribed' => Subscriber::factory()
            ->unsubscribed()
            ->create(),
        'suppressed' => Subscriber::factory()
            ->suppressed()
            ->create(),
    ];
    $query = Subscriber::query();

    $status->scope($query);

    $ids = $query->pluck('id')
        ->all();

    expect($ids)
        ->toBe([$subscribers[$visible]->getKey()]);
})->with([
    'active' => [SubscriberStatus::Active, 'active'],
    'pending' => [SubscriberStatus::Pending, 'pending'],
    'unsubscribed' => [SubscriberStatus::Unsubscribed, 'unsubscribed'],
    'suppressed' => [SubscriberStatus::Suppressed, 'suppressed'],
]);

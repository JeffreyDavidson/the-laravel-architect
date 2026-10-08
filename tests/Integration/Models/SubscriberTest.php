<?php

use App\Enums\SubscriberStatus;
use App\Enums\SuppressionReason;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;

use function Pest\Laravel\freezeTime;

pest()->use(RefreshDatabase::class);

it('only counts verified subscriptions that have not unsubscribed as active', function () {
    $active = Subscriber::factory()->create();
    $pending = Subscriber::factory()
        ->pending()
        ->create();
    $unsubscribed = Subscriber::factory()
        ->unsubscribed()
        ->create();

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

it('never treats a suppressed reader as active or prunes it', function () {
    freezeTime();
    $suppressed = Subscriber::factory()
        ->suppressed(SuppressionReason::Complained)
        ->create([
            'subscribed_at' => now()->subYear(),
            'unsubscribed_at' => now()->subYear(),
            'suppressed_at' => now()->subYear(),
        ]);
    $flagged = Subscriber::factory()->create(['suppressed_at' => now()]);

    $anyActive = Subscriber::query()
        ->active()
        ->exists();
    $anyPrunable = new Subscriber()->prunable()
        ->exists();

    expect($anyActive)
        ->toBeFalse()
        ->and($suppressed->isSuppressed())
        ->toBeTrue()
        ->and($flagged->isActive())
        ->toBeFalse()
        ->and($suppressed->suppression_reason)
        ->toBe(SuppressionReason::Complained)
        ->and($anyPrunable)
        ->toBeFalse();
});

it('reports suppression before unsubscribing and unsubscribing before confirmation', function (?string $verifiedAt, ?string $unsubscribedAt, ?string $suppressedAt, SubscriberStatus $expected) {
    $subscriber = new Subscriber([
        'verified_at' => $verifiedAt,
        'unsubscribed_at' => $unsubscribedAt,
        'suppressed_at' => $suppressedAt,
    ]);

    $status = $subscriber->status();

    expect($status)
        ->toBe($expected);
})->with([
    'confirmed' => ['2026-09-01', null, null, SubscriberStatus::Active],
    'never confirmed' => [null, null, null, SubscriberStatus::Pending],
    'confirmed then unsubscribed' => ['2026-09-01', '2026-09-10', null, SubscriberStatus::Unsubscribed],
    'unsubscribed before confirming' => [null, '2026-09-10', null, SubscriberStatus::Unsubscribed],
    'suppressed after unsubscribing' => ['2026-09-01', '2026-09-10', '2026-09-10', SubscriberStatus::Suppressed],
    'suppressed before confirming' => [null, null, '2026-09-10', SubscriberStatus::Suppressed],
    'suppressed without unsubscribing' => ['2026-09-01', null, '2026-09-10', SubscriberStatus::Suppressed],
]);

it('narrows a query to exactly the subscribers that report each status', function (SubscriberStatus $status) {
    $dates = [null, '2026-09-10'];
    $combinations = array_map(
        fn (array $combination): array => [
            'verified_at' => $combination[0],
            'unsubscribed_at' => $combination[1],
            'suppressed_at' => $combination[2],
        ],
        Arr::crossJoin($dates, $dates, $dates),
    );
    $subscribers = array_map(
        fn (array $attributes): Subscriber => Subscriber::factory()->create($attributes),
        $combinations,
    );
    $expected = collect($subscribers)
        ->filter(fn (Subscriber $subscriber): bool => $subscriber->status() === $status)
        ->pluck('id')
        ->values()
        ->all();

    $ids = Subscriber::query()
        ->withStatus($status)
        ->pluck('id')
        ->all();

    expect($ids)
        ->toBe($expected)
        ->not->toBeEmpty();
})->with(SubscriberStatus::cases());

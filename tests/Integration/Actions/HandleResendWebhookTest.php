<?php

use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Actions\HandleResendWebhook;
use JeffreyDavidson\CreatorKit\Enums\SuppressionReason;

pest()->use(RefreshDatabase::class);

covers(HandleResendWebhook::class);

function suppressedSubscriberCount(): int
{
    return Subscriber::query()
        ->whereNotNull('suppressed_at')
        ->count();
}

it('suppresses the recipient of a permanent bounce', function () {
    $reader = Subscriber::factory()->create(['email' => 'reader@example.com']);

    $suppressed = app(HandleResendWebhook::class)->handle([
        'type' => 'email.bounced',
        'data' => ['to' => ['Reader@Example.com'], 'bounce' => ['type' => 'Permanent']],
    ]);

    $reader->refresh();

    expect($suppressed)
        ->toBe(1)
        ->and($reader->suppression_reason)
        ->toBe(SuppressionReason::Bounced);
});

it('ignores a transient bounce', function () {
    $reader = Subscriber::factory()->create();

    $suppressed = app(HandleResendWebhook::class)->handle([
        'type' => 'email.bounced',
        'data' => ['to' => [$reader->email], 'bounce' => ['type' => 'Transient']],
    ]);

    $reader->refresh();

    expect($suppressed)
        ->toBe(0)
        ->and($reader->isActive())
        ->toBeTrue();
});

it('records the reason for complaints and provider suppressions', function (string $type, SuppressionReason $reason) {
    $reader = Subscriber::factory()->create();

    app(HandleResendWebhook::class)->handle(['type' => $type, 'data' => ['to' => [$reader->email]]]);

    $reader->refresh();

    expect($reader->suppression_reason)
        ->toBe($reason);
})->with([
    'complaint' => ['email.complained', SuppressionReason::Complained],
    'provider suppression' => ['email.suppressed', SuppressionReason::Bounced],
]);

it('suppresses every known recipient of an event and skips unknown ones', function () {
    $first = Subscriber::factory()->create();
    $second = Subscriber::factory()->create();

    $suppressed = app(HandleResendWebhook::class)->handle([
        'type' => 'email.complained',
        'data' => ['to' => [$first->email, 'stranger@example.com', $second->email]],
    ]);

    expect($suppressed)
        ->toBe(2)
        ->and(suppressedSubscriberCount())
        ->toBe(2);
});

it('changes nothing for other events and malformed payloads', function (array $event) {
    /** @var array<array-key, mixed> $event */
    $reader = Subscriber::factory()->create();

    $suppressed = app(HandleResendWebhook::class)->handle($event);

    $reader->refresh();

    expect($suppressed)
        ->toBe(0)
        ->and($reader->isActive())
        ->toBeTrue();
})->with([
    'delivered' => [['type' => 'email.delivered', 'data' => ['to' => ['reader@example.com']]]],
    'no type' => [['data' => ['to' => ['reader@example.com']]]],
    'no recipients' => [['type' => 'email.complained', 'data' => []]],
    'recipients not a list' => [['type' => 'email.complained', 'data' => ['to' => 'reader@example.com']]],
    'non-string recipient' => [['type' => 'email.complained', 'data' => ['to' => [42, null]]]],
    'bounce without a type' => [['type' => 'email.bounced', 'data' => ['to' => ['reader@example.com']]]],
]);

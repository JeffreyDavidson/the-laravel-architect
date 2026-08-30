<?php

use App\Actions\ConfirmNewsletterSubscription;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('marks a subscriber as verified and clears pending state', function () {
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now()->subDay(),
        'unsubscribed_at' => now()->subHour(),
    ]);
    $subscriber->verification_token_hash = hash('sha256', 'confirmation-token');
    $subscriber->save();

    app(ConfirmNewsletterSubscription::class)($subscriber);

    expect($subscriber->refresh()->verified_at)->not->toBeNull()
        ->and($subscriber->unsubscribed_at)->toBeNull()
        ->and($subscriber->verification_token_hash)->toBeNull();
});

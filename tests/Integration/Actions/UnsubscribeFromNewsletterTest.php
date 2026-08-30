<?php

use App\Actions\UnsubscribeFromNewsletter;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('marks a subscriber as unsubscribed and clears pending confirmation state', function () {
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now()->subDay(),
        'verified_at' => now()->subHour(),
    ]);
    $subscriber->verification_token_hash = hash('sha256', 'confirmation-token');
    $subscriber->save();

    app(UnsubscribeFromNewsletter::class)($subscriber);

    expect($subscriber->refresh()->unsubscribed_at)->not->toBeNull()
        ->and($subscriber->verification_token_hash)->toBeNull();
});

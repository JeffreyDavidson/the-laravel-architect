<?php

use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Actions\ConfirmNewsletterSubscription;

pest()->use(RefreshDatabase::class);

it('marks a subscriber as verified and clears pending state', function () {
    $subscriber = Subscriber::factory()
        ->pending()
        ->create(['unsubscribed_at' => now()->subHour()]);
    $subscriber->verification_token_hash = hash('sha256', 'confirmation-token');
    $subscriber->save();

    app(ConfirmNewsletterSubscription::class)
        ->handle($subscriber);

    expect($subscriber->refresh()
        ->verified_at)->not->toBeNull()
        ->and($subscriber->unsubscribed_at)
        ->toBeNull()
        ->and($subscriber->verification_token_hash)
        ->toBeNull();
});

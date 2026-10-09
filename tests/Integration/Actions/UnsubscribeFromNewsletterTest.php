<?php

use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Actions\UnsubscribeFromNewsletter;

pest()->use(RefreshDatabase::class);

it('marks a subscriber as unsubscribed and clears pending confirmation state', function () {
    $subscriber = Subscriber::factory()->create();
    $subscriber->verification_token_hash = hash('sha256', 'confirmation-token');
    $subscriber->save();

    app(UnsubscribeFromNewsletter::class)
        ->handle($subscriber);

    expect($subscriber->refresh()
        ->unsubscribed_at)->not->toBeNull()
        ->and($subscriber->verification_token_hash)
        ->toBeNull();
});

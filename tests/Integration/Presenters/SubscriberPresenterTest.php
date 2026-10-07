<?php

use App\Models\Subscriber;
use App\Presenters\SubscriberPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\freezeSecond;

covers(SubscriberPresenter::class);

pest()->use(RefreshDatabase::class);

it('signs a confirmation link for the token that expires after a day', function () {
    freezeSecond();
    $subscriber = Subscriber::factory()
        ->pending()
        ->create();

    $url = SubscriberPresenter::from($subscriber)->confirmationUrl('plain-token');

    expect($url)
        ->toBe(URL::temporarySignedRoute('newsletter.confirm', now()->addDay(), ['subscriber' => $subscriber, 'token' => 'plain-token']));
});

it('signs an unsubscribe link that never expires', function () {
    $subscriber = Subscriber::factory()->create();

    $url = SubscriberPresenter::from($subscriber)->unsubscribeUrl();

    expect($url)
        ->toBe(URL::signedRoute('newsletter.unsubscribe', ['subscriber' => $subscriber]))
        ->not->toContain('expires=');
});

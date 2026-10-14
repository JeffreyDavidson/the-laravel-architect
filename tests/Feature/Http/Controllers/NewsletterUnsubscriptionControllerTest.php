<?php

use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use JeffreyDavidson\CreatorKit\Presenters\SubscriberPresenter;

use function Pest\Laravel\call;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\travel;

pest()->use(RefreshDatabase::class);

describe('create', function () {
    it('submits only the confirmation page by itself, never the unsubscribe page', function () {
        Mail::fake();

        $token = 'valid-confirmation-token';
        $subscriber = Subscriber::factory()->create(['verification_token_hash' => hash('sha256', $token)]);
        $confirmUrl = URL::temporarySignedRoute(
            'newsletter.confirm',
            now()->addHour(),
            ['subscriber' => $subscriber, 'token' => $token],
        );
        $unsubscribeUrl = SubscriberPresenter::from($subscriber)
            ->unsubscribeUrl();

        $confirmPage = get($confirmUrl);
        $unsubscribePage = get($unsubscribeUrl);

        $confirmPage->assertSeeHtml('data-newsletter-confirm');
        $unsubscribePage->assertOk()
            ->assertDontSeeHtml('data-newsletter-confirm');
    });

    it('shows an unsubscribe step without changing subscriber state', function () {
        Mail::fake();

        $subscriber = Subscriber::factory()->create();

        $url = SubscriberPresenter::from($subscriber)
            ->unsubscribeUrl();
        $email = $subscriber->email;

        $response = get($url);

        $response
            ->assertOk()
            ->assertSee('Unsubscribe from the newsletter')
            ->assertSeeHtml('name="_method" value="DELETE"')
            ->assertSee($email)
            ->assertSeeHtml('<meta name="robots" content="noindex, nofollow">');

        $subscriber->refresh();
        expect($subscriber->unsubscribed_at)
            ->toBeNull();
    });

    it('generates unsubscribe links that keep working after the newsletter is sent', function () {
        Mail::fake();

        $subscriber = Subscriber::factory()->create();
        $url = SubscriberPresenter::from($subscriber)
            ->unsubscribeUrl();

        travel(1)
            ->year();

        $response = get($url);

        $response
            ->assertOk();
        expect($url)
            ->not
            ->toContain('expires=');
    });

    it('keeps the unsubscribe page out of every cache', function () {
        Mail::fake();

        $subscriber = Subscriber::factory()->create();
        $url = SubscriberPresenter::from($subscriber)
            ->unsubscribeUrl();

        $response = get($url);

        $response->assertHeader('Cache-Control', 'no-store, private');
    });
});

describe('store', function () {
    it('unsubscribes with an explicit delete to a valid signed link', function () {
        Mail::fake();

        $subscriber = Subscriber::factory()->create();

        $url = SubscriberPresenter::from($subscriber)
            ->unsubscribeUrl();

        $response = delete($url);

        $response
            ->assertRedirect(route('home').'#newsletter-form')
            ->assertSessionHas('newsletter_success', 'You have been unsubscribed.');

        $subscriber->refresh();
        expect($subscriber->unsubscribed_at)
            ->not
            ->toBeNull();
    });

    it('rejects unsigned unsubscribe requests', function () {
        Mail::fake();

        $subscriber = Subscriber::factory()->create();

        $response = delete(route('newsletter.unsubscribe.store', $subscriber));

        $response
            ->assertForbidden();

        $subscriber->refresh();
        expect($subscriber->unsubscribed_at)
            ->toBeNull();
    });

    it('rejects expired unsubscribe links', function () {
        Mail::fake();

        $subscriber = Subscriber::factory()->create();
        $url = URL::temporarySignedRoute(
            'newsletter.unsubscribe',
            now()->subMinute(),
            ['subscriber' => $subscriber],
        );

        $response = delete($url);

        $response
            ->assertForbidden();

        $subscriber->refresh();
        expect($subscriber->unsubscribed_at)
            ->toBeNull();
    });
});

describe('shared contracts', function () {
    it('rejects an unsigned unsubscribe link without revealing whether the subscriber exists', function (string $method, string $routeName) {
        Mail::fake();

        $subscriber = Subscriber::factory()->create();

        $existingSubscriber = call($method, route($routeName, $subscriber));
        $missingSubscriber = call($method, route($routeName, $subscriber->id + 1));

        $existingSubscriber->assertForbidden();
        $missingSubscriber->assertForbidden();
    })->with([
        'unsubscribe page' => ['GET', 'newsletter.unsubscribe'],
        'unsubscribe form' => ['DELETE', 'newsletter.unsubscribe.store'],
        'one-click unsubscribe' => ['POST', 'newsletter.unsubscribe.oneClick'],
    ]);
});

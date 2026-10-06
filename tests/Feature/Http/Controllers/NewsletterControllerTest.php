<?php

use App\Mail\ConfirmNewsletterSubscription;
use App\Models\Subscriber;
use App\Support\Newsletter\UnsubscribeUrlGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\call;
use function Pest\Laravel\followingRedirects;
use function Pest\Laravel\post;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
});

it('creates an unverified subscriber and sends a confirmation message', function () {
    $this->post(route('newsletter.subscribe'), ['email' => 'Reader@Example.com'])
        ->assertRedirect()
        ->assertSessionHas('newsletter_success', 'Check your email to confirm your subscription.');

    $subscriber = Subscriber::query()->sole();

    $verifiedAt = $subscriber->verified_at;
    $tokenHash = $subscriber->verification_token_hash;
    expect($subscriber->email)
        ->toBe('reader@example.com')
        ->and($verifiedAt)
        ->toBeNull()
        ->and($tokenHash)
        ->not
        ->toBeNull();

    Mail::assertQueued(ConfirmNewsletterSubscription::class, 1);
});

it('applies the pending email cooldown across different source IP addresses', function () {
    $url = route('newsletter.subscribe');

    $this->call('POST', $url, ['email' => 'reader@example.com'], [], [], [
        'REMOTE_ADDR' => '192.0.2.10',
    ])->assertRedirect();

    $this->call('POST', $url, ['email' => 'reader@example.com'], [], [], [
        'REMOTE_ADDR' => '198.51.100.20',
    ])->assertRedirect();

    Mail::assertQueued(ConfirmNewsletterSubscription::class, 1);
});

it('silently accepts newsletter honeypot submissions without subscribing', function () {
    $this->post(route('newsletter.subscribe'), [
        'website' => 'filled-by-bot',
    ])->assertSessionHas('newsletter_success');

    expect(Subscriber::query()->count())
        ->toBe(0);
    Mail::assertNothingQueued();
});

it('shows an explicit confirmation step without changing subscriber state', function () {
    $token = 'valid-confirmation-token';
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
    ]);
    $subscriber->verification_token_hash = hash('sha256', $token);
    $subscriber->save();

    $url = URL::temporarySignedRoute(
        'newsletter.confirm',
        now()->addHour(),
        ['subscriber' => $subscriber, 'token' => $token],
    );

    $email = $subscriber->email;

    $this->get($url)
        ->assertOk()
        ->assertSee('Confirm your subscription')
        ->assertSee($email)
        ->assertSeeHtml('<meta name="robots" content="noindex, nofollow">');

    $subscriber->refresh();
    $tokenHash = $subscriber->verification_token_hash;
    expect($subscriber->verified_at)
        ->toBeNull()
        ->and($tokenHash)
        ->not
        ->toBeNull();
});

it('keeps the confirmation page out of every cache, including the back-forward cache', function () {
    $token = 'valid-confirmation-token';
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
    ]);
    $subscriber->verification_token_hash = hash('sha256', $token);
    $subscriber->save();
    $url = URL::temporarySignedRoute(
        'newsletter.confirm',
        now()->addHour(),
        ['subscriber' => $subscriber, 'token' => $token],
    );

    $response = $this->get($url);

    $response->assertHeader('Cache-Control', 'no-store, private');
});

it('renders the confirmation page in its confirming state with a no-script button inside the signed form', function () {
    $token = 'valid-confirmation-token';
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
    ]);
    $subscriber->verification_token_hash = hash('sha256', $token);
    $subscriber->save();
    $url = URL::temporarySignedRoute(
        'newsletter.confirm',
        now()->addHour(),
        ['subscriber' => $subscriber, 'token' => $token],
    );

    $escapedUrl = e($url);

    $response = $this->get($url);

    $response->assertOk()
        ->assertSeeHtmlInOrder([
            '<h1',
            'Confirming your subscription…',
            '</h1>',
            '<form',
            "action=\"{$escapedUrl}\"",
            'method="POST"',
            'name="_token"',
            'One moment.',
            '<noscript>',
            'Confirm that you want newsletter updates sent to reader@example.com.',
            'type="submit"',
            'Confirm subscription',
            '</noscript>',
            '</form>',
        ])
        ->assertDontSeeHtml('>Confirm your subscription</h1>');
});

it('titles the confirmation page tab with the confirming state', function () {
    $token = 'valid-confirmation-token';
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
    ]);
    $subscriber->verification_token_hash = hash('sha256', $token);
    $subscriber->save();
    $url = URL::temporarySignedRoute(
        'newsletter.confirm',
        now()->addHour(),
        ['subscriber' => $subscriber, 'token' => $token],
    );

    $response = $this->get($url);

    $response->assertOk()
        ->assertSeeHtml('<title>Confirming Your Subscription')
        ->assertDontSeeHtml('<title>Confirm Your Subscription');
});

it('submits only the confirmation page by itself, never the unsubscribe page', function () {
    $token = 'valid-confirmation-token';
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
        'verified_at' => now(),
    ]);
    $subscriber->verification_token_hash = hash('sha256', $token);
    $subscriber->save();
    $confirmUrl = URL::temporarySignedRoute(
        'newsletter.confirm',
        now()->addHour(),
        ['subscriber' => $subscriber, 'token' => $token],
    );
    $unsubscribeUrl = app(UnsubscribeUrlGenerator::class)
        ->for($subscriber);

    $confirmPage = $this->get($confirmUrl);
    $unsubscribePage = $this->get($unsubscribeUrl);

    $confirmPage->assertSeeHtml('data-newsletter-confirm');
    $unsubscribePage->assertOk()
        ->assertDontSeeHtml('data-newsletter-confirm');
});

it('limits confirmation link requests to 10 a minute from one address', function () {
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
    ]);
    $subscriber->verification_token_hash = hash('sha256', 'valid-token');
    $subscriber->save();
    $url = URL::temporarySignedRoute(
        'newsletter.confirm',
        now()->addHour(),
        ['subscriber' => $subscriber, 'token' => 'valid-token'],
    );
    foreach (range(1, 10) as $attempt) {
        $this->get($url)
            ->assertOk();
    }

    $response = $this->get($url);

    $response->assertTooManyRequests();
});

it('confirms a subscriber with an explicit post to a valid signed link and redirects to the confirmed page', function () {
    $token = 'valid-confirmation-token';
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
    ]);
    $subscriber->verification_token_hash = hash('sha256', $token);
    $subscriber->save();

    $url = URL::temporarySignedRoute(
        'newsletter.confirm',
        now()->addHour(),
        ['subscriber' => $subscriber, 'token' => $token],
    );

    $this->post($url)
        ->assertRedirect(route('newsletter.confirmed'));

    $subscriber->refresh();
    $tokenHash = $subscriber->verification_token_hash;
    expect($subscriber->verified_at)
        ->not
        ->toBeNull()
        ->and($tokenHash)
        ->toBeNull();
});

it('sends an unusable confirmation link back to the signup form without confirming anyone', function (Closure $link, string $method, string $routeName) {
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
    ]);
    $subscriber->verification_token_hash = hash('sha256', 'valid-token');
    $subscriber->save();
    $url = $link($subscriber, $routeName);
    if (! is_string($url)) {
        throw new RuntimeException('The dataset must build a confirmation URL.');
    }

    call($method, $url)
        ->assertRedirect(route('home').'#newsletter-form')
        ->assertSessionHasErrors(['email' => 'This confirmation link has expired or has already been used. If you already confirmed, you’re subscribed. Otherwise, sign up again below.']);

    $subscriber->refresh();
    $tokenHash = $subscriber->verification_token_hash;
    expect($subscriber->verified_at)
        ->toBeNull()
        ->and($tokenHash)
        ->toBe(hash('sha256', 'valid-token'));
})->with([
    'expired signature' => [fn (Subscriber $subscriber, string $routeName): string => URL::temporarySignedRoute(
        $routeName,
        now()->subMinute(),
        ['subscriber' => $subscriber, 'token' => 'valid-token'],
    )],
    'tampered signature' => [fn (Subscriber $subscriber, string $routeName): string => str_replace(
        'expires=',
        'expires=1',
        URL::temporarySignedRoute($routeName, now()->addHour(), ['subscriber' => $subscriber, 'token' => 'valid-token']),
    )],
    'unsigned link' => [fn (Subscriber $subscriber, string $routeName): string => route(
        $routeName,
        ['subscriber' => $subscriber, 'token' => 'valid-token'],
    )],
    'unknown token' => [fn (Subscriber $subscriber, string $routeName): string => URL::temporarySignedRoute(
        $routeName,
        now()->addHour(),
        ['subscriber' => $subscriber, 'token' => 'unknown-token'],
    )],
    'unknown subscriber' => [fn (Subscriber $subscriber, string $routeName): string => URL::temporarySignedRoute(
        $routeName,
        now()->addHour(),
        ['subscriber' => $subscriber->id + 1, 'token' => 'valid-token'],
    )],
])->with([
    'confirmation page' => ['GET', 'newsletter.confirm'],
    'confirmation form' => ['POST', 'newsletter.confirm.store'],
]);

it('sends a confirmation link used a second time back to the signup form with its message', function () {
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
    ]);
    $subscriber->verification_token_hash = hash('sha256', 'valid-token');
    $subscriber->save();
    $url = URL::temporarySignedRoute(
        'newsletter.confirm',
        now()->addHour(),
        ['subscriber' => $subscriber, 'token' => 'valid-token'],
    );
    post($url);

    $response = followingRedirects()
        ->get($url);

    $response->assertSeeText('This confirmation link has expired or has already been used. If you already confirmed, you’re subscribed. Otherwise, sign up again below.');
    $subscriber->refresh();
    expect($subscriber->verified_at)
        ->not
        ->toBeNull();
});

it('shows an unsubscribe step without changing subscriber state', function () {
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
        'verified_at' => now(),
    ]);

    $url = app(UnsubscribeUrlGenerator::class)
        ->for($subscriber);
    $email = $subscriber->email;

    $this->get($url)
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
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
        'verified_at' => now(),
    ]);
    $url = app(UnsubscribeUrlGenerator::class)
        ->for($subscriber);

    $this->travel(1)
        ->year();

    $this->get($url)
        ->assertOk();
    expect($url)
        ->not
        ->toContain('expires=');
});

it('unsubscribes with an explicit delete to a valid signed link', function () {
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
        'verified_at' => now(),
    ]);

    $url = app(UnsubscribeUrlGenerator::class)
        ->for($subscriber);

    $this->delete($url)
        ->assertRedirect(route('home').'#newsletter-form')
        ->assertSessionHas('newsletter_success', 'You have been unsubscribed.');

    $subscriber->refresh();
    expect($subscriber->unsubscribed_at)
        ->not
        ->toBeNull();
});

it('rejects unsigned unsubscribe requests', function () {
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
    ]);

    $this->delete(route('newsletter.unsubscribe.store', $subscriber))
        ->assertForbidden();

    $subscriber->refresh();
    expect($subscriber->unsubscribed_at)
        ->toBeNull();
});

it('rejects expired unsubscribe links', function () {
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
    ]);
    $url = URL::temporarySignedRoute(
        'newsletter.unsubscribe',
        now()->subMinute(),
        ['subscriber' => $subscriber],
    );

    $this->delete($url)
        ->assertForbidden();

    $subscriber->refresh();
    expect($subscriber->unsubscribed_at)
        ->toBeNull();
});

it('does not disclose whether an email is already subscribed', function () {
    Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
        'verified_at' => now(),
    ]);

    $this->post(route('newsletter.subscribe'), ['email' => 'reader@example.com'])
        ->assertSessionHas('newsletter_success', 'Check your email to confirm your subscription.');

    Mail::assertNothingQueued();
});

it('sends a subscriber back to the signup form after subscribing', function (string $page) {
    $this->from(route($page))
        ->post(route('newsletter.subscribe'), ['email' => 'reader@example.com'])
        ->assertRedirect(route($page).'#newsletter-form')
        ->assertSessionHas('newsletter_success');
})->with([
    'home page' => ['home'],
    'newsletter page' => ['newsletter.index'],
]);

it('sends a bot back to the signup form too, without revealing the honeypot', function () {
    $this->from(route('home'))
        ->post(route('newsletter.subscribe'), ['email' => 'bot@example.com', 'website' => 'https://spam.example'])
        ->assertRedirect(route('home').'#newsletter-form')
        ->assertSessionHas('newsletter_success');
});

it('sends a rejected sign-up back to the signup form with its error', function () {
    $this->from(route('home'))
        ->post(route('newsletter.subscribe'), ['email' => 'not-an-email'])
        ->assertRedirect(route('home').'#newsletter-form')
        ->assertSessionHasErrors('email')
        ->assertSessionHasInput('email', 'not-an-email');
});

it('renders the signup form with the anchor the redirects point to', function (string $page) {
    $this->get(route($page))
        ->assertOk()
        ->assertSeeHtml('id="newsletter-form"');
})->with([
    'home page' => ['home'],
    'newsletter page' => ['newsletter.index'],
]);

it('answers a script request with the confirmation message and keeps the session untouched', function () {
    $this->postJson(route('newsletter.subscribe'), ['email' => 'Reader@Example.com'])
        ->assertOk()
        ->assertExactJson(['message' => 'Check your email to confirm your subscription.'])
        ->assertSessionMissing('newsletter_success');

    $subscriber = Subscriber::query()->sole();

    expect($subscriber->email)
        ->toBe('reader@example.com');
    Mail::assertQueued(ConfirmNewsletterSubscription::class);
});

it('answers a script request for a rejected address with the validation error', function () {
    $this->postJson(route('newsletter.subscribe'), ['email' => 'not-an-email'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    expect(Subscriber::query()->count())
        ->toBe(0);
});

it('answers a bot that fills the honeypot like a real sign-up, without subscribing anyone', function () {
    $this->postJson(route('newsletter.subscribe'), ['email' => 'bot@example.com', 'website' => 'https://spam.example'])
        ->assertOk()
        ->assertExactJson(['message' => 'Check your email to confirm your subscription.']);

    expect(Subscriber::query()->count())
        ->toBe(0);
    Mail::assertNothingQueued();
});

it('renders the signup form as an Alpine component with its live region', function (string $page) {
    $this->get(route($page))
        ->assertOk()
        ->assertSeeHtml('x-data="newsletterForm"')
        ->assertSeeHtml('data-newsletter-form')
        ->assertSeeHtml('data-newsletter-feedback')
        ->assertSeeHtml('id="newsletter-email-error-live"');
})->with([
    'home page' => ['home'],
    'newsletter page' => ['newsletter.index'],
]);

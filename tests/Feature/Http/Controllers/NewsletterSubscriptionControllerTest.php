<?php

use App\Mail\NewsletterConfirmationMail;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\call;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\postJson;

pest()->use(RefreshDatabase::class);

it('flashes invalid newsletter input for recovery', function () {
    $response = from(route('home'))
        ->post(route('newsletter.subscribe'), ['email' => 'not-an-email']);

    $response
        ->assertSessionHasErrors('email')
        ->assertSessionHasInput('email', 'not-an-email');

});

it('creates an unverified subscriber and sends a confirmation message', function () {
    Mail::fake();

    $response = post(route('newsletter.subscribe'), ['email' => 'Reader@Example.com']);

    $response
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

    Mail::assertQueued(NewsletterConfirmationMail::class, 1);
});

it('applies the pending email cooldown across different source IP addresses', function () {
    Mail::fake();

    $url = route('newsletter.subscribe');

    $response = call('POST', $url, ['email' => 'reader@example.com'], [], [], [
        'REMOTE_ADDR' => '192.0.2.10',
    ]);

    $response->assertRedirect();

    $response = call('POST', $url, ['email' => 'reader@example.com'], [], [], [
        'REMOTE_ADDR' => '198.51.100.20',
    ]);

    $response->assertRedirect();

    Mail::assertQueued(NewsletterConfirmationMail::class, 1);
});

it('silently accepts newsletter honeypot submissions without subscribing', function () {
    Mail::fake();

    $response = post(route('newsletter.subscribe'), [
        'website' => 'filled-by-bot',
    ]);

    $response->assertSessionHas('newsletter_success');

    expect(Subscriber::query()->count())
        ->toBe(0);
    Mail::assertNothingQueued();
});

it('limits newsletter sign-ups to 5 an hour from one address', function () {
    Mail::fake();

    foreach (range(1, 5) as $attempt) {
        post(route('newsletter.subscribe'), ['email' => "reader{$attempt}@example.com"])
            ->assertRedirect();
    }

    $response = post(route('newsletter.subscribe'), ['email' => 'reader6@example.com']);

    $response->assertTooManyRequests();
    Mail::assertQueued(NewsletterConfirmationMail::class, 5);
});

it('does not disclose whether an email is already subscribed', function () {
    Mail::fake();

    Subscriber::factory()->create([
        'email' => 'reader@example.com',
    ]);

    $response = post(route('newsletter.subscribe'), ['email' => 'reader@example.com']);

    $response
        ->assertSessionHas('newsletter_success', 'Check your email to confirm your subscription.');

    Mail::assertNothingQueued();
});

it('sends a subscriber back to the signup form after subscribing', function (string $page) {
    Mail::fake();

    $response = from(route($page))
        ->post(route('newsletter.subscribe'), ['email' => 'reader@example.com']);

    $response
        ->assertRedirect(route($page).'#newsletter-form')
        ->assertSessionHas('newsletter_success');
})->with([
    'home page' => ['home'],
    'newsletter page' => ['newsletter.index'],
]);

it('sends a bot back to the signup form too, without revealing the honeypot', function () {
    Mail::fake();

    $response = from(route('home'))
        ->post(route('newsletter.subscribe'), ['email' => 'bot@example.com', 'website' => 'https://spam.example']);

    $response
        ->assertRedirect(route('home').'#newsletter-form')
        ->assertSessionHas('newsletter_success');
});

it('sends a rejected sign-up back to the signup form with its error', function () {
    Mail::fake();

    $response = from(route('home'))
        ->post(route('newsletter.subscribe'), ['email' => 'not-an-email']);

    $response
        ->assertRedirect(route('home').'#newsletter-form')
        ->assertSessionHasErrors('email')
        ->assertSessionHasInput('email', 'not-an-email');
});

it('renders the signup form with the anchor the redirects point to', function (string $page) {
    Mail::fake();

    $response = get(route($page));

    $response
        ->assertOk()
        ->assertSeeHtml('id="newsletter-form"');
})->with([
    'home page' => ['home'],
    'newsletter page' => ['newsletter.index'],
]);

it('answers a script request with the confirmation message and keeps the session untouched', function () {
    Mail::fake();

    $response = postJson(route('newsletter.subscribe'), ['email' => 'Reader@Example.com']);

    $response
        ->assertOk()
        ->assertExactJson(['message' => 'Check your email to confirm your subscription.'])
        ->assertSessionMissing('newsletter_success');

    $subscriber = Subscriber::query()->sole();

    expect($subscriber->email)
        ->toBe('reader@example.com');
    Mail::assertQueued(NewsletterConfirmationMail::class);
});

it('answers a script request for a rejected address with the validation error', function () {
    Mail::fake();

    $response = postJson(route('newsletter.subscribe'), ['email' => 'not-an-email']);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    expect(Subscriber::query()->count())
        ->toBe(0);
});

it('answers a bot that fills the honeypot like a real sign-up, without subscribing anyone', function () {
    Mail::fake();

    $response = postJson(route('newsletter.subscribe'), ['email' => 'bot@example.com', 'website' => 'https://spam.example']);

    $response
        ->assertOk()
        ->assertExactJson(['message' => 'Check your email to confirm your subscription.']);

    expect(Subscriber::query()->count())
        ->toBe(0);
    Mail::assertNothingQueued();
});

it('renders the signup form as an Alpine component with its live region', function (string $page) {
    Mail::fake();

    $response = get(route($page));

    $response
        ->assertOk()
        ->assertSeeHtml('x-data="newsletterForm"')
        ->assertSeeHtml('data-newsletter-form')
        ->assertSeeHtml('data-newsletter-feedback')
        ->assertSeeHtml('id="newsletter-email-error-live"');
})->with([
    'home page' => ['home'],
    'newsletter page' => ['newsletter.index'],
]);

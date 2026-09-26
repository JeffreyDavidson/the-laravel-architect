<?php

use App\Mail\ConfirmNewsletterSubscription;
use App\Models\Subscriber;
use App\Support\Newsletter\UnsubscribeUrlGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

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

it('confirms a subscriber with an explicit post to a valid signed link', function () {
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
        ->assertRedirect(route('home'))
        ->assertSessionHas('newsletter_success');

    $subscriber->refresh();
    $tokenHash = $subscriber->verification_token_hash;
    expect($subscriber->verified_at)
        ->not
        ->toBeNull()
        ->and($tokenHash)
        ->toBeNull();
});

it('rejects unsigned newsletter state changes', function () {
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
    ]);
    $subscriber->verification_token_hash = hash('sha256', 'token');
    $subscriber->save();

    $this->post(route('newsletter.confirm.store', [$subscriber, 'token']))
        ->assertForbidden();

    $subscriber->refresh();
    expect($subscriber->verified_at)
        ->toBeNull();
});

it('rejects signed confirmation links with an invalid token', function () {
    $subscriber = Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
    ]);
    $subscriber->verification_token_hash = hash('sha256', 'valid-token');
    $subscriber->save();

    foreach (['newsletter.confirm', 'newsletter.confirm.store'] as $routeName) {
        $url = URL::temporarySignedRoute(
            $routeName,
            now()->addHour(),
            ['subscriber' => $subscriber, 'token' => 'invalid-token'],
        );

        $this->call(
            $routeName === 'newsletter.confirm' ? 'GET' : 'POST',
            $url,
        )->assertForbidden();
    }

    $subscriber->refresh();
    $tokenHash = $subscriber->verification_token_hash;
    expect($subscriber->verified_at)
        ->toBeNull()
        ->and($tokenHash)
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
        ->assertRedirect(route('home'))
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

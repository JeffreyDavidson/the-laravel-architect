<?php

use App\Mail\ContactMessageConfirmation;
use App\Mail\ContactMessageReceived;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    config()->set([
        'services.turnstile.site_key' => 'test-site-key',
        'services.turnstile.secret_key' => 'test-secret-key',
        'services.turnstile.siteverify_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
        'services.turnstile.contact_action' => 'contact-form',
        'services.turnstile.allowed_hostnames' => ['thelaravelarchitect.com', 'www.thelaravelarchitect.com'],
    ]);

    RateLimiter::clear('contact-form:127.0.0.1');
    Mail::fake();
});

it('renders the Turnstile widget on the contact page', function () {
    $response = $this->get(route('contact'));

    $response
        ->assertOk()
        ->assertSee('data-sitekey="test-site-key"', false)
        ->assertSee('data-action="contact-form"', false);

    expect(substr_count($response->getContent(), 'https://challenges.cloudflare.com/turnstile/v0/api.js'))
        ->toBe(1);
});

it('silently accepts honeypot submissions without sending mail', function () {
    $this->post(route('contact.submit'), [
        'name' => 'Spam Bot',
        'email' => 'spam@example.com',
        'type' => 'freelance',
        'budget' => 'small',
        'message' => 'This should not send.',
        'website' => 'filled-by-bot',
    ])->assertSessionHas('success');

    Mail::assertNothingQueued();
    Http::assertNothingSent();
});

it('queues both contact messages after a valid submission', function () {
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
            'action' => 'contact-form',
            'hostname' => 'thelaravelarchitect.com',
        ]),
    ]);

    $this->post(route('contact.submit'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'type' => 'consulting',
        'message' => 'Can you help with an audit?',
        'cf-turnstile-response' => 'valid-token',
    ])->assertSessionHas('success');

    Mail::assertQueued(
        ContactMessageReceived::class,
        fn (ContactMessageReceived $mail): bool => $mail->senderEmail === 'jane@example.com'
            && str_contains($mail->render(), 'Can you help with an audit?'),
    );
    Mail::assertQueued(
        ContactMessageConfirmation::class,
        fn (ContactMessageConfirmation $mail): bool => $mail->senderName === 'Jane Doe'
            && str_contains($mail->render(), 'Here\'s a copy of your message.'),
    );
    expect(RateLimiter::attempts('contact-form:127.0.0.1'))->toBe(1);
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
        && $request['secret'] === 'test-secret-key'
        && $request['response'] === 'valid-token'
        && $request['remoteip'] === '127.0.0.1');
});

it('rejects a contact submission when Turnstile verification fails', function () {
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => false]),
    ]);

    $response = $this->post(route('contact.submit'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'type' => 'consulting',
        'message' => 'Can you help with an audit?',
        'cf-turnstile-response' => 'invalid-token',
    ]);

    $response
        ->assertSessionHasErrors('cf-turnstile-response')
        ->assertSessionHasInput('name', 'Jane Doe');

    expect(RateLimiter::attempts('contact-form:127.0.0.1'))->toBe(0);
    expect(session()->getOldInput('cf-turnstile-response'))->toBeNull();
    Mail::assertNothingQueued();
});

it('rejects Turnstile responses with invalid request context', function (array $turnstileResponse) {
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response($turnstileResponse),
    ]);

    $this->post(route('contact.submit'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'type' => 'consulting',
        'message' => 'Can you help with an audit?',
        'cf-turnstile-response' => 'valid-token',
    ])->assertSessionHasErrors('cf-turnstile-response');

    expect(RateLimiter::attempts('contact-form:127.0.0.1'))->toBe(0);
    Mail::assertNothingQueued();
})->with([
    'wrong hostname' => [[
        'success' => true,
        'action' => 'contact-form',
        'hostname' => 'attacker.example',
    ]],
    'missing hostname' => [[
        'success' => true,
        'action' => 'contact-form',
    ]],
    'wrong action' => [[
        'success' => true,
        'action' => 'newsletter',
        'hostname' => 'thelaravelarchitect.com',
    ]],
    'missing action' => [[
        'success' => true,
        'hostname' => 'thelaravelarchitect.com',
    ]],
]);

it('fails closed when the Turnstile secret is missing', function () {
    config()->set('services.turnstile.secret_key');

    $this->post(route('contact.submit'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'type' => 'consulting',
        'message' => 'Can you help with an audit?',
        'cf-turnstile-response' => 'valid-token',
    ])->assertSessionHasErrors('cf-turnstile-response');

    Http::assertNothingSent();
    Mail::assertNothingQueued();
});

it('fails closed when Turnstile cannot be reached', function () {
    Http::fake(fn () => throw new ConnectionException('Turnstile unavailable.'));

    $this->post(route('contact.submit'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'type' => 'consulting',
        'message' => 'Can you help with an audit?',
        'cf-turnstile-response' => 'valid-token',
    ])->assertSessionHasErrors('cf-turnstile-response');

    expect(RateLimiter::attempts('contact-form:127.0.0.1'))->toBe(0);
    Mail::assertNothingQueued();
});

it('does not count invalid submissions against the rate limit', function () {
    $this->from(route('contact'))
        ->post(route('contact.submit'), [
            'name' => 'Jane Doe',
            'email' => 'not-an-email',
            'type' => 'consulting',
            'budget' => 'medium',
            'message' => '',
        ])
        ->assertRedirect(route('contact'))
        ->assertSessionHasErrors(['email', 'message']);

    expect(session()->getOldInput())->toMatchArray([
        'name' => 'Jane Doe',
        'email' => 'not-an-email',
        'type' => 'consulting',
        'budget' => 'medium',
    ]);

    expect(RateLimiter::attempts('contact-form:127.0.0.1'))->toBe(0);
    Mail::assertNothingQueued();
    Http::assertNothingSent();
});

it('renders preserved values and accessible validation feedback', function () {
    $this->from(route('contact'))
        ->post(route('contact.submit'), [
            'name' => 'Jane Doe',
            'email' => 'not-an-email',
            'type' => 'modernization',
            'budget' => 'large',
            'message' => '',
        ]);

    $this->get(route('contact'))
        ->assertOk()
        ->assertSee('Please review the highlighted fields.')
        ->assertSee('value="Jane Doe"', false)
        ->assertSee('value="modernization" selected', false)
        ->assertSee('value="large" selected', false)
        ->assertSee('aria-invalid="true" aria-describedby="email-error"', false)
        ->assertSee('href="#email"', false)
        ->assertSee('id="email-error"', false);
});

it('rate limits repeated contact submissions by ip address', function () {
    RateLimiter::hit('contact-form:127.0.0.1', 3600);
    RateLimiter::hit('contact-form:127.0.0.1', 3600);
    RateLimiter::hit('contact-form:127.0.0.1', 3600);

    $this->post(route('contact.submit'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'type' => 'consulting',
        'message' => 'Can you help with an audit?',
        'website' => '',
    ])->assertSessionHasErrors('message');

    expect(session()->getOldInput())->toMatchArray([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'type' => 'consulting',
        'message' => 'Can you help with an audit?',
    ]);

    expect(session()->getOldInput())->not->toHaveKey('website');

    Mail::assertNothingQueued();
    Http::assertNothingSent();
});

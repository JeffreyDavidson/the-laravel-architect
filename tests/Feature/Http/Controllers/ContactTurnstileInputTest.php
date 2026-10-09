<?php

use App\Models\ContactInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\from;

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set([
        'creator-kit.turnstile.secret_key' => 'test-secret',
        'creator-kit.turnstile.siteverify_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
        'creator-kit.turnstile.contact_action' => 'contact-form',
        'creator-kit.turnstile.allowed_hostnames' => ['thelaravelarchitect.com'],
    ]);
    Http::fake();
});

it('answers a malformed Turnstile token with the verification error, not a server error', function (mixed $token) {
    $response = from(route('contact.create'))
        ->post(route('contact.store'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'type' => 'consulting',
            'message' => 'Can you help with an audit?',
            'website' => '',
            'cf-turnstile-response' => $token,
        ]);

    $response
        ->assertRedirect(route('contact.create'))
        ->assertSessionHasErrors('cf-turnstile-response');
    Http::assertNothingSent();
    expect(ContactInquiry::query()->count())
        ->toBe(0);
})->with([
    'array' => [['unexpected']],
    'nested array' => [[['unexpected']]],
    'number' => [123],
]);

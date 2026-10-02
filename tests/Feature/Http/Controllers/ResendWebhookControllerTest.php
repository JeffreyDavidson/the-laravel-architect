<?php

use App\Http\Controllers\ResendWebhookController;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\call;

covers(ResendWebhookController::class);

pest()->use(RefreshDatabase::class);

const WEBHOOK_SECRET = 'whsec_'.'dGVzdC1zZWNyZXQtdmFsdWU=';

beforeEach(function () {
    config()->set('services.resend.webhook_secret', WEBHOOK_SECRET);
});

/** @return array<string, string> */
function signedWebhookHeaders(string $body, ?int $timestamp = null, string $secret = WEBHOOK_SECRET): array
{
    $timestamp ??= time();
    $key = base64_decode(substr($secret, 6));
    $signature = base64_encode(hash_hmac('sha256', "msg_1.{$timestamp}.{$body}", $key, true));

    return [
        'HTTP_SVIX_ID' => 'msg_1',
        'HTTP_SVIX_TIMESTAMP' => (string) $timestamp,
        'HTTP_SVIX_SIGNATURE' => "v1,{$signature}",
        'CONTENT_TYPE' => 'application/json',
    ];
}

/**
 * @param  array<string, mixed>  $event
 * @return TestResponse<Response>
 */
function postWebhook(array $event): TestResponse
{
    $body = json_encode($event, JSON_THROW_ON_ERROR);

    return call('POST', route('webhooks.resend'), [], [], [], signedWebhookHeaders($body), $body);
}

/** @return array<string, mixed> */
function complaintEvent(string $email): array
{
    return ['type' => 'email.complained', 'data' => ['to' => [$email]]];
}

it('suppresses the subscriber for a verified complaint', function () {
    $reader = Subscriber::factory()->create();

    postWebhook(complaintEvent($reader->email))->assertNoContent();

    $reader->refresh();

    expect($reader->isSuppressed())
        ->toBeTrue();
});

it('accepts and ignores a verified event it does not use', function () {
    $reader = Subscriber::factory()->create();

    postWebhook(['type' => 'email.delivered', 'data' => ['to' => [$reader->email]]])->assertNoContent();

    $reader->refresh();

    expect($reader->isActive())
        ->toBeTrue();
});

it('is harmless when the same event is delivered twice', function () {
    $reader = Subscriber::factory()->create();

    postWebhook(complaintEvent($reader->email))->assertNoContent();
    postWebhook(complaintEvent($reader->email))->assertNoContent();

    $suppressed = Subscriber::query()
        ->whereNotNull('suppressed_at')
        ->count();

    expect($suppressed)
        ->toBe(1);
});

it('rejects a request with a bad signature and changes nothing', function (string $case) {
    $reader = Subscriber::factory()->create();
    $body = json_encode(complaintEvent($reader->email), JSON_THROW_ON_ERROR);
    $server = [
        'missing headers' => ['CONTENT_TYPE' => 'application/json'],
        'old timestamp' => signedWebhookHeaders($body, time() - 3600),
        'wrong secret' => signedWebhookHeaders($body, null, 'whsec_'.base64_encode('another-secret')),
        'tampered body' => signedWebhookHeaders('{"type":"email.complained","data":{"to":["x@example.com"]}}'),
    ][$case];

    call('POST', route('webhooks.resend'), [], [], [], $server, $body)->assertForbidden();

    $reader->refresh();

    expect($reader->isActive())
        ->toBeTrue();
})->with(['missing headers', 'old timestamp', 'wrong secret', 'tampered body']);

it('answers service unavailable until the secret is configured', function (?string $secret) {
    config()->set('services.resend.webhook_secret', $secret);
    $reader = Subscriber::factory()->create();

    postWebhook(complaintEvent($reader->email))->assertServiceUnavailable();

    $reader->refresh();

    expect($reader->isActive())
        ->toBeTrue();
})->with([null, '']);

it('rejects a signed body that is not JSON', function () {
    $body = 'not json';

    call('POST', route('webhooks.resend'), [], [], [], signedWebhookHeaders($body), $body)->assertUnprocessable();
});

it('uses no session or forgery middleware', function () {
    $route = Route::getRoutes()->getByName('webhooks.resend');

    expect($route?->excludedMiddleware())
        ->toContain('web')
        ->and($route?->gatherMiddleware())
        ->toContain('throttle:resend-webhook');
});

it('is rate limited per address', function () {
    $reader = Subscriber::factory()->create();

    foreach (range(1, 60) as $attempt) {
        postWebhook(complaintEvent($reader->email))->assertNoContent();
    }

    postWebhook(complaintEvent($reader->email))->assertTooManyRequests();
});

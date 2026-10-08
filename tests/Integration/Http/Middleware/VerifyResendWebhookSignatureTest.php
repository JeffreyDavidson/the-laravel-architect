<?php

use App\Http\Middleware\VerifyResendWebhookSignature;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

covers(VerifyResendWebhookSignature::class);

const RESEND_SIGNING_SECRET = 'whsec_'.'bWlkZGxld2FyZS10ZXN0LXNlY3JldA==';

const RESEND_WEBHOOK_BODY = '{"type":"email.complained","data":{"to":["reader@example.com"]}}';

beforeEach(function () {
    config()->set('services.resend.webhook_secret', RESEND_SIGNING_SECRET);
});

function resendSignature(string $body, int $timestamp, string $secret = RESEND_SIGNING_SECRET): string
{
    $key = base64_decode(substr($secret, 6));

    return 'v1,'.base64_encode(hash_hmac('sha256', "msg_1.{$timestamp}.{$body}", $key, true));
}

/** @param array<string, string> $headers */
function resendWebhookRequest(array $headers, string $body = RESEND_WEBHOOK_BODY): Request
{
    $server = collect($headers)
        ->mapWithKeys(fn (string $value, string $name): array => ['HTTP_'.strtoupper(str_replace('-', '_', $name)) => $value])
        ->all();

    return Request::create('/webhooks/resend', 'POST', [], [], [], $server, $body);
}

/** @return array<string, string> */
function signedResendHeaders(string $body = RESEND_WEBHOOK_BODY, ?int $timestamp = null, string $secret = RESEND_SIGNING_SECRET): array
{
    $timestamp ??= time();

    return [
        'svix-id' => 'msg_1',
        'svix-timestamp' => (string) $timestamp,
        'svix-signature' => resendSignature($body, $timestamp, $secret),
    ];
}

function runResendSignatureCheck(Request $request): Response
{
    return new VerifyResendWebhookSignature()->handle($request, fn (): Response => new Response('handled'));
}

it('passes a correctly signed request on', function (array $headers) {
    /** @var array<string, string> $headers */
    $response = runResendSignatureCheck(resendWebhookRequest($headers));

    expect($response->getStatusCode())
        ->toBe(200)
        ->and($response->getContent())
        ->toBe('handled');
})->with([
    'one signature' => fn (): array => signedResendHeaders(),
    'a valid signature among several' => function (): array {
        $headers = signedResendHeaders();
        $headers['svix-signature'] = 'v1,'.base64_encode('not-the-signature').' '.$headers['svix-signature'];

        return $headers;
    },
]);

it('answers service unavailable without checking the request until the secret is configured', function (?string $secret) {
    config()->set('services.resend.webhook_secret', $secret);

    $response = runResendSignatureCheck(resendWebhookRequest(signedResendHeaders()));

    expect($response->getStatusCode())
        ->toBe(503)
        ->and($response->getContent())
        ->toBe('');
})->with([
    'missing' => [null],
    'empty' => [''],
]);

it('forbids a request whose signature does not verify', function (Request $request) {
    $response = runResendSignatureCheck($request);

    expect($response->getStatusCode())
        ->toBe(403)
        ->and($response->getContent())
        ->toBe('');
})->with([
    'missing headers' => fn (): Request => resendWebhookRequest([]),
    'old timestamp' => fn (): Request => resendWebhookRequest(signedResendHeaders(timestamp: time() - 3600)),
    'future timestamp' => fn (): Request => resendWebhookRequest(signedResendHeaders(timestamp: time() + 3600)),
    'wrong secret' => fn (): Request => resendWebhookRequest(signedResendHeaders(secret: 'whsec_'.base64_encode('another-secret'))),
    'tampered body' => fn (): Request => resendWebhookRequest(signedResendHeaders(), '{"type":"email.complained","data":{"to":["x@example.com"]}}'),
    'signature without a value' => fn (): Request => resendWebhookRequest(['svix-signature' => 'v1'] + signedResendHeaders()),
    'signature pair missing its value' => function (): Request {
        $headers = signedResendHeaders();
        $headers['svix-signature'] .= ' v1';

        return resendWebhookRequest($headers);
    },
]);

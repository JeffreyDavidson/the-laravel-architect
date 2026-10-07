<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\HandleResendWebhook;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Resend\Exceptions\WebhookSignatureVerificationException;
use Resend\WebhookSignature;

/**
 * Receives Resend's signed delivery events. Resend posts server-to-server
 * without a session or forgery token, so the signature is the only credential.
 * Payloads and addresses are never logged.
 */
final class ResendWebhookController
{
    public function __invoke(Request $request, HandleResendWebhook $handleResendWebhook): Response
    {
        $secret = config('services.resend.webhook_secret');

        if (! is_string($secret) || $secret === '') {
            return response()->noContent(503);
        }

        $signature = $request->header('svix-signature', '');

        // The SDK reads each space-separated "version,signature" pair without checking it has both parts.
        if (! is_string($signature) || preg_match('/\A[^\s,]+,\S+(?: [^\s,]+,\S+)*\z/', $signature) !== 1) {
            return response()->noContent(403);
        }

        $body = $request->getContent();

        try {
            WebhookSignature::verify($body, [
                'svix-id' => $request->header('svix-id', ''),
                'svix-timestamp' => $request->header('svix-timestamp', ''),
                'svix-signature' => $signature,
            ], $secret);
        } catch (WebhookSignatureVerificationException) {
            return response()->noContent(403);
        }

        $event = json_decode($body, true);

        if (! is_array($event)) {
            return response()->noContent(422);
        }

        $handleResendWebhook->handle($event);

        return response()->noContent();
    }
}

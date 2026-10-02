<?php

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
class ResendWebhookController
{
    public function __invoke(Request $request, HandleResendWebhook $handleResendWebhook): Response
    {
        $secret = config('services.resend.webhook_secret');

        if (! is_string($secret) || $secret === '') {
            return response()->noContent(503);
        }

        $body = $request->getContent();

        try {
            WebhookSignature::verify($body, [
                'svix-id' => $request->header('svix-id', ''),
                'svix-timestamp' => $request->header('svix-timestamp', ''),
                'svix-signature' => $request->header('svix-signature', ''),
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

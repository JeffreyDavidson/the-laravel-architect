<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Resend\Exceptions\WebhookSignatureVerificationException;
use Resend\WebhookSignature;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets a Resend webhook through only with a valid svix signature on its exact
 * body and a fresh timestamp. Resend posts server-to-server without a session
 * or forgery token, so the signature is the only credential. Answers 503 until
 * the signing secret is configured and 403 for any unsigned or tampered request.
 */
final class VerifyResendWebhookSignature
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
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

        try {
            WebhookSignature::verify($request->getContent(), [
                'svix-id' => $request->header('svix-id', ''),
                'svix-timestamp' => $request->header('svix-timestamp', ''),
                'svix-signature' => $signature,
            ], $secret);
        } catch (WebhookSignatureVerificationException) {
            return response()->noContent(403);
        }

        return $next($request);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\HandleResendWebhook;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Receives Resend's delivery events. VerifyResendWebhookSignature has already
 * checked the signature on the route, so this only decodes the body and hands
 * it to the action. Payloads and addresses are never logged.
 */
final class ResendWebhookController
{
    public function __invoke(Request $request, HandleResendWebhook $handleResendWebhook): Response
    {
        $event = json_decode($request->getContent(), true);

        if (! is_array($event)) {
            return response()->noContent(422);
        }

        $handleResendWebhook->handle($event);

        return response()->noContent();
    }
}

<?php

declare(strict_types=1);

namespace App\Mail\Concerns;

use Carbon\CarbonInterface;

/**
 * Builds the stable part of a mailable's `Resend-Idempotency-Key` header. The
 * fingerprint identifies the record the email is about across environments and
 * database resets, so a retried send reuses the key and Resend, which keeps a key
 * for 24 hours, delivers it once.
 */
trait FingerprintsIdempotencyKeys
{
    private function idempotencyFingerprint(?int $recordId, ?CarbonInterface $createdAt): string
    {
        return hash('sha256', implode('|', [
            config()->string('app.url'),
            $recordId,
            $createdAt?->toISOString(),
        ]));
    }
}

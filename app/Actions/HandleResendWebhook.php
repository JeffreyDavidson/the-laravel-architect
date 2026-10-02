<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SuppressionReason;

final readonly class HandleResendWebhook
{
    public function __construct(private SuppressSubscriber $suppressSubscriber) {}

    /**
     * Suppresses the recipients of a verified Resend event that means the
     * address must not be mailed again (a permanent bounce, a spam complaint
     * or Resend's own suppression). Returns how many subscribers were
     * suppressed; every other event or malformed payload returns 0.
     *
     * @param  array<array-key, mixed>  $event
     */
    public function handle(array $event): int
    {
        $data = $event['data'] ?? null;

        if (! is_array($data)) {
            return 0;
        }

        $reason = match ($event['type'] ?? null) {
            'email.bounced' => $this->isPermanentBounce($data) ? SuppressionReason::Bounced : null,
            'email.suppressed' => SuppressionReason::Bounced,
            'email.complained' => SuppressionReason::Complained,
            default => null,
        };
        $recipients = $data['to'] ?? null;

        if ($reason === null || ! is_array($recipients)) {
            return 0;
        }

        $suppressed = 0;

        foreach ($recipients as $recipient) {
            if (is_string($recipient) && $this->suppressSubscriber->handle($recipient, $reason)) {
                $suppressed++;
            }
        }

        return $suppressed;
    }

    /** @param array<array-key, mixed> $data */
    private function isPermanentBounce(array $data): bool
    {
        $bounce = $data['bounce'] ?? null;

        return is_array($bounce) && ($bounce['type'] ?? null) === 'Permanent';
    }
}

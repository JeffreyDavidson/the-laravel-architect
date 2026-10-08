<?php

declare(strict_types=1);

namespace App\Rules;

use App\Services\TurnstileVerifier;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Rejects a submission unless Cloudflare Turnstile confirms the submitted token for the
 * expected action. Implicit, so a missing token fails instead of being skipped.
 */
final class PassesTurnstile implements ValidationRule
{
    public bool $implicit = true;

    public function __construct(
        private readonly TurnstileVerifier $verifier,
        private readonly ?string $clientIp,
        private readonly string $expectedAction,
    ) {}

    /**
     * Verify the submitted token, sent with the visitor's IP; a missing or non-string token fails.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! $this->verifier->verify($value, $this->clientIp, $this->expectedAction)) {
            $fail('Please verify that you are human and try again.');
        }
    }
}

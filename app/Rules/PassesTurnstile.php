<?php

declare(strict_types=1);

namespace App\Rules;

use App\Services\TurnstileVerifier;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Request;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Rejects a submission unless Cloudflare Turnstile confirms the request's token for the
 * expected action. Implicit, so a missing token fails instead of being skipped.
 */
final class PassesTurnstile implements ValidationRule
{
    public bool $implicit = true;

    public function __construct(
        private readonly TurnstileVerifier $verifier,
        private readonly Request $request,
        private readonly string $expectedAction,
    ) {}

    /**
     * The verifier reads the token and client IP from the request itself, so the
     * validated value is not used.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->verifier->passes($this->request, $this->expectedAction)) {
            $fail('Please verify that you are human and try again.');
        }
    }
}

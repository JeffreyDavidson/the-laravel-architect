<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use App\Rules\PassesTurnstile;
use App\Services\TurnstileVerifier;
use Closure;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Symfony\Component\HttpFoundation\Response;

/**
 * The public forms' spam checks, for a FormRequest. A bot that fills the hidden `website` field
 * gets the normal success response and nothing is saved; start the request's rules() with
 * `if ($this->isHoneypotSubmission()) { return []; }`. Turnstile is checked only once every other
 * field is valid, so a rejected or honeypot submission never makes the remote call, and a failed
 * check goes back without the spent token. The request keeps its own fields, messages, error bag
 * and redirect, and must not define after(), failedValidation() or passedValidation() itself.
 */
trait ChecksForSpam
{
    private const string HONEYPOT_FIELD = 'website';

    private const string TURNSTILE_FIELD = 'cf-turnstile-response';

    /** The response a real submission gets, which a honeypot submission receives too. */
    abstract protected function honeypotResponse(): Response;

    /** The Turnstile action the form's widget sends. Override it for a form other than contact. */
    protected function turnstileAction(): string
    {
        return Config::string('services.turnstile.contact_action');
    }

    protected function isHoneypotSubmission(): bool
    {
        return $this->filled(self::HONEYPOT_FIELD);
    }

    /** @return list<Closure(Validator): void> */
    public function after(TurnstileVerifier $turnstileVerifier): array
    {
        return [
            function (Validator $validator) use ($turnstileVerifier): void {
                $errors = $validator->errors();

                if ($this->isHoneypotSubmission() || $errors->isNotEmpty()) {
                    return;
                }

                $turnstile = validator(
                    [self::TURNSTILE_FIELD => $this->input(self::TURNSTILE_FIELD)],
                    [self::TURNSTILE_FIELD => [new PassesTurnstile($turnstileVerifier, $this->ip(), $this->turnstileAction())]],
                );

                $errors->merge($turnstile->errors());
            },
        ];
    }

    /** A failed Turnstile check goes back without the spent token; other failures keep the default flash. */
    protected function failedValidation(ValidatorContract $validator): void
    {
        if ($validator->errors()
            ->has(self::TURNSTILE_FIELD)) {
            throw new ValidationException($validator, $this->redirector->to($this->getRedirectUrl())
                ->withErrors($validator, $this->errorBag)
                ->withInput($this->except(self::TURNSTILE_FIELD)));
        }

        parent::failedValidation($validator);
    }

    protected function passedValidation(): void
    {
        if ($this->isHoneypotSubmission()) {
            throw new HttpResponseException($this->honeypotResponse());
        }
    }
}

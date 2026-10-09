<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Data\ContactMessageData;
use App\Enums\ContactBudget;
use App\Enums\ContactType;
use App\Models\Project;
use App\Queries\PublishedProjectQuery;
use Closure;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use JeffreyDavidson\CreatorKit\Rules\PassesTurnstile;
use JeffreyDavidson\CreatorKit\Services\TurnstileVerifier;

final class StoreContactRequest extends FormRequest
{
    public const string SENT_MESSAGE = 'Message sent! I\'ll get back to you within 24–48 hours. A copy has been sent to your email.';

    private const string TURNSTILE_FIELD = 'cf-turnstile-response';

    public function toData(): ContactMessageData
    {
        $validated = $this->safe();
        $project = $this->project();

        return new ContactMessageData(
            name: $validated->string('name')
                ->toString(),
            email: $validated->string('email')
                ->toString(),
            type: ContactType::from($validated->string('type')
                ->toString()),
            budget: $validated->enum('budget', ContactBudget::class),
            message: $validated->string('message')
                ->toString(),
            projectTitle: $project?->title,
        );
    }

    /** The published project the visitor is asking about, if any. */
    public function project(): ?Project
    {
        $slug = $this->string('project')
            ->trim()
            ->toString();

        return app(PublishedProjectQuery::class)->findBySlug($slug);
    }

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string|Enum|Exists>> */
    public function rules(): array
    {
        if ($this->isHoneypotSubmission()) {
            return [];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'type' => ['required', 'string', Rule::enum(ContactType::class)],
            'budget' => ['nullable', 'string', Rule::enum(ContactBudget::class)],
            'message' => ['required', 'string', 'max:5000'],
            'project' => [
                'nullable',
                'string',
                'max:255',
                Rule::exists('projects', 'slug')->where('status', PublishStatus::Published->value),
            ],
        ];
    }

    /**
     * Verify Turnstile only once every other field is valid, so a rejected or honeypot
     * submission never makes the remote verification call.
     *
     * @return list<Closure(Validator): void>
     */
    public function after(TurnstileVerifier $turnstileVerifier): array
    {
        return [
            function (Validator $validator) use ($turnstileVerifier): void {
                $errors = $validator->errors();

                if ($this->isHoneypotSubmission() || $errors->isNotEmpty()) {
                    return;
                }

                $action = config('creator-kit.turnstile.contact_action');
                $turnstile = validator(
                    [self::TURNSTILE_FIELD => $this->input(self::TURNSTILE_FIELD)],
                    [self::TURNSTILE_FIELD => [new PassesTurnstile($turnstileVerifier, $this->ip(), is_string($action) ? $action : '')]],
                );

                $errors->merge($turnstile->errors());
            },
        ];
    }

    /** A failed Turnstile check is sent back without the spent token; other failures keep the default flash. */
    protected function failedValidation(ValidatorContract $validator): void
    {
        if ($validator->errors()
            ->has(self::TURNSTILE_FIELD)) {
            throw new ValidationException($validator, back()
                ->withErrors($validator)
                ->withInput($this->except(self::TURNSTILE_FIELD)));
        }

        parent::failedValidation($validator);
    }

    /** Bots that fill the hidden website field get the normal success response, and nothing is sent. */
    protected function passedValidation(): void
    {
        if ($this->isHoneypotSubmission()) {
            throw new HttpResponseException(back()->with('success', self::SENT_MESSAGE));
        }
    }

    private function isHoneypotSubmission(): bool
    {
        return $this->filled('website');
    }
}

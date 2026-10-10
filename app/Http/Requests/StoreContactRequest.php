<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Data\ContactMessageData;
use App\Enums\ContactBudget;
use App\Enums\ContactType;
use App\Http\Requests\Concerns\ChecksForSpam;
use App\Models\Project;
use App\Queries\PublishedProjectQuery;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Exists;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;

final class StoreContactRequest extends FormRequest
{
    use ChecksForSpam;

    public const string SENT_MESSAGE = 'Message sent! I\'ll get back to you within 24–48 hours. A copy has been sent to your email.';

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

    /** Bots that fill the hidden website field get the normal success response, and nothing is sent. */
    protected function honeypotResponse(): RedirectResponse
    {
        return back()->with('success', self::SENT_MESSAGE);
    }
}

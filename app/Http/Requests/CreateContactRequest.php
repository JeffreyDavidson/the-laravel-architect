<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The contact page's query string. A "discuss this project" link passes the project's slug;
 * it is only a hint, so an unusable value is dropped instead of failing the page.
 */
final class CreateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'project' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** The trimmed project slug, or an empty string when there is none. */
    public function projectSlug(): string
    {
        $slug = $this->validated('project');

        return is_string($slug) ? $slug : '';
    }

    protected function prepareForValidation(): void
    {
        $project = $this->input('project');

        $this->merge([
            'project' => is_string($project) && mb_strlen(trim($project)) <= 255
                ? trim($project)
                : null,
        ]);
    }
}

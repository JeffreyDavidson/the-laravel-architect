<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

final class ProjectIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'technology' => ['nullable', 'string', 'max:120'],
            'tag' => ['nullable', 'string', 'max:120'],
        ];
    }

    /** The trimmed technology filter, or null when none was chosen. */
    public function technology(): ?string
    {
        return $this->filter('technology');
    }

    /** The trimmed topic tag slug filter, or null when none was chosen. */
    public function tag(): ?string
    {
        return $this->filter('tag');
    }

    protected function prepareForValidation(): void
    {
        $technology = $this->input('technology');
        $tag = $this->input('tag');

        $this->merge([
            'technology' => is_string($technology) && filled($technology) ? trim($technology) : $technology,
            'tag' => is_string($tag) && filled($tag) ? trim($tag) : $tag,
        ]);
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($validator->errors()
            ->isNotEmpty()) {
            abort(404);
        }

        parent::failedValidation($validator);
    }

    private function filter(string $key): ?string
    {
        $value = $this->validated($key);

        return is_string($value) && filled($value) ? $value : null;
    }
}

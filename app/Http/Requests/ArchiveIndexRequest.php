<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\SearchContentType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArchiveIndexRequest extends FormRequest
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
            'type' => ['nullable', 'string', Rule::enum(SearchContentType::class)],
            'year' => ['nullable', 'integer', 'min:2000', 'max:'.now()->year],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $type = $this->input('type');

        $this->merge([
            'type' => is_string($type) && filled($type) ? trim($type) : $type,
        ]);
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            abort(404);
        }

        parent::failedValidation($validator);
    }
}

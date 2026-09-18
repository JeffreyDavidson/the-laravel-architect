<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\SearchContentType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchRequest extends FormRequest
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
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', 'string', Rule::enum(SearchContentType::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $query = $this->input('q');

        $this->merge([
            'q' => is_string($query) && filled($query) ? trim($query) : $query,
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

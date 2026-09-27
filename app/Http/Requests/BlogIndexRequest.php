<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlogIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:120', Rule::exists('categories', 'slug')],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $query = $this->input('q');
        $category = $this->input('category');

        $this->merge([
            'q' => is_string($query) && filled($query) ? trim($query) : $query,
            'category' => is_string($category) && filled($category) ? trim($category) : $category,
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
}

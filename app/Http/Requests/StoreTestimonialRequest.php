<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        if ($this->filled('website')) {
            return [];
        }

        return [
            'name' => ['required', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:100'],
            'body' => ['required', 'string', 'max:1000'],
        ];
    }

    /** @return array{name: string, role: ?string, company: ?string, body: string} */
    public function testimonialAttributes(): array
    {
        return [
            'name' => $this->safe()->string('name')->toString(),
            'role' => $this->filled('role')
                ? $this->safe()->string('role')->toString()
                : null,
            'company' => $this->filled('company')
                ? $this->safe()->string('company')->toString()
                : null,
            'body' => $this->safe()->string('body')->toString(),
        ];
    }
}

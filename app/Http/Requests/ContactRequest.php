<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'type' => ['required', 'string', 'in:freelance,consulting,modernization,collaboration,other'],
            'budget' => ['nullable', 'string', 'in:small,medium,large,enterprise'],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }
}

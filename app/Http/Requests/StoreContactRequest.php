<?php

namespace App\Http\Requests;

use App\Data\ContactMessageData;
use App\Enums\ContactBudget;
use App\Enums\ContactType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreContactRequest extends FormRequest
{
    public function toData(): ContactMessageData
    {
        $validated = $this->safe();

        return new ContactMessageData(
            name: $validated->string('name')->toString(),
            email: $validated->string('email')->toString(),
            type: ContactType::from($validated->string('type')->toString()),
            budget: $validated->enum('budget', ContactBudget::class),
            message: $validated->string('message')->toString(),
        );
    }

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string|Enum>> */
    public function rules(): array
    {
        if ($this->filled('website')) {
            return [];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'type' => ['required', 'string', Rule::enum(ContactType::class)],
            'budget' => ['nullable', 'string', Rule::enum(ContactBudget::class)],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }
}

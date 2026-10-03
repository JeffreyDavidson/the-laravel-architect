<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeNewsletterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Send a rejected sign-up back to the signup form, not to the top of the page. */
    protected function getRedirectUrl(): string
    {
        return parent::getRedirectUrl().'#newsletter-form';
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        if ($this->filled('website')) {
            return [];
        }

        return [
            'email' => ['required', 'email', 'max:255'],
        ];
    }
}

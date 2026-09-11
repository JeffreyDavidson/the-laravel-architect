<?php

namespace App\Http\Controllers;

use App\Actions\SendContactMessage;
use App\Http\Requests\StoreContactRequest;
use App\Services\TurnstileVerifier;
use App\ViewModels\ContactViewModel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\RateLimiter;

class ContactController
{
    public function create(ContactViewModel $viewModel): View
    {
        return view('pages.contact', $viewModel->data());
    }

    public function store(
        StoreContactRequest $request,
        TurnstileVerifier $turnstileVerifier,
        SendContactMessage $sendContactMessage,
    ): RedirectResponse {
        if ($request->filled('website')) {
            return back()->with('success', 'Message sent! I\'ll get back to you within 24–48 hours. A copy has been sent to your email.');
        }

        $key = 'contact-form:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()
                ->withErrors(['message' => 'Too many submissions. Please try again later.'])
                ->withInput($request->except(['website', 'cf-turnstile-response']));
        }

        $turnstileAction = config('services.turnstile.contact_action');

        if (! is_string($turnstileAction) || ! $turnstileVerifier->passes($request, $turnstileAction)) {
            return back()
                ->withErrors([
                    'cf-turnstile-response' => 'Please verify that you are human and try again.',
                ])
                ->withInput($request->except('cf-turnstile-response'));
        }

        RateLimiter::hit($key, 3600);

        $sendContactMessage->handle($request->toData());

        return back()->with('success', 'Message sent! I\'ll get back to you within 24–48 hours. A copy has been sent to your email.');
    }
}

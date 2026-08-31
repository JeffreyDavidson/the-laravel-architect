<?php

namespace App\Http\Controllers;

use App\Actions\SendContactMessage;
use App\Http\Requests\ContactRequest;
use App\Services\TurnstileVerifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\RateLimiter;

class SubmitContactController extends Controller
{
    public function __invoke(
        ContactRequest $request,
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

        $validated = $request->safe();
        $name = $validated->string('name')->toString();
        $email = $validated->string('email')->toString();
        $type = $validated->string('type')->toString();
        $budget = $validated->has('budget')
            ? $validated->string('budget')->toString()
            : null;
        $message = $validated->string('message')->toString();
        RateLimiter::hit($key, 3600);

        $sendContactMessage(
            name: $name,
            email: $email,
            type: $type,
            budget: $budget,
            message: $message,
        );

        return back()->with('success', 'Message sent! I\'ll get back to you within 24–48 hours. A copy has been sent to your email.');
    }
}

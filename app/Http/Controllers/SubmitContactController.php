<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessageConfirmation;
use App\Mail\ContactMessageReceived;
use App\Support\Turnstile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class SubmitContactController extends Controller
{
    public function __invoke(ContactRequest $request, Turnstile $turnstile): RedirectResponse
    {
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

        if (! is_string($turnstileAction) || ! $turnstile->passes($request, $turnstileAction)) {
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

        Mail::to(config('mail.contact_to', config('mail.from.address')))->queue(new ContactMessageReceived(
            senderName: $name,
            senderEmail: $email,
            contactType: $type,
            budget: $budget,
            contactMessage: $message,
        ));
        Mail::to($email, $name)->queue(new ContactMessageConfirmation(
            senderName: $name,
            contactType: $type,
            budget: $budget,
            contactMessage: $message,
        ));

        return back()->with('success', 'Message sent! I\'ll get back to you within 24–48 hours. A copy has been sent to your email.');
    }
}

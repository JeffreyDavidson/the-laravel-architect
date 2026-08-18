<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessageConfirmation;
use App\Mail\ContactMessageReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class ContactController extends Controller
{
    public function submit(ContactRequest $request): RedirectResponse
    {
        if ($request->filled('website')) {
            return back()->with('success', 'Message sent! I\'ll get back to you within 24–48 hours. A copy has been sent to your email.');
        }

        $validated = $request->validated();
        $key = 'contact-form:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->withErrors(['message' => 'Too many submissions. Please try again later.']);
        }

        RateLimiter::hit($key, 3600);

        Mail::to(config('mail.contact_to', config('mail.from.address')))->queue(new ContactMessageReceived(
            senderName: $validated['name'],
            senderEmail: $validated['email'],
            contactType: $validated['type'],
            budget: $validated['budget'] ?? null,
            contactMessage: $validated['message'],
        ));
        Mail::to($validated['email'], $validated['name'])->queue(new ContactMessageConfirmation(
            senderName: $validated['name'],
            contactType: $validated['type'],
            budget: $validated['budget'] ?? null,
            contactMessage: $validated['message'],
        ));

        return back()->with('success', 'Message sent! I\'ll get back to you within 24–48 hours. A copy has been sent to your email.');
    }
}

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

        $validated = $request->safe();
        $name = $validated->string('name')->toString();
        $email = $validated->string('email')->toString();
        $type = $validated->string('type')->toString();
        $budget = $validated->has('budget')
            ? $validated->string('budget')->toString()
            : null;
        $message = $validated->string('message')->toString();
        $key = 'contact-form:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()
                ->withErrors(['message' => 'Too many submissions. Please try again later.'])
                ->withInput($request->except('website'));
        }

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

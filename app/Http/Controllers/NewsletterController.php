<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmNewsletterSubscription;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    public function subscribe(Request $request): RedirectResponse
    {
        if ($request->filled('website')) {
            return back()->with('newsletter_success', 'Check your email to confirm your subscription.');
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $subscriber = Subscriber::query()->firstOrNew(['email' => Str::lower($validated['email'])]);

        if ($subscriber->verified_at && ! $subscriber->unsubscribed_at) {
            return back()->with('newsletter_success', 'Check your email to confirm your subscription.');
        }

        $token = Str::random(64);

        $subscriber->fill([
            'subscribed_at' => now(),
            'verified_at' => null,
            'verification_token' => hash('sha256', $token),
            'unsubscribed_at' => null,
        ]);
        $subscriber->save();

        $confirmationUrl = URL::temporarySignedRoute(
            'newsletter.confirm',
            now()->addDay(),
            ['subscriber' => $subscriber, 'token' => $token],
        );

        Mail::to($subscriber->email)->send(new ConfirmNewsletterSubscription($confirmationUrl));

        return back()->with('newsletter_success', 'Check your email to confirm your subscription.');
    }

    public function confirm(Subscriber $subscriber, string $token): RedirectResponse
    {
        abort_unless(
            $subscriber->verification_token
                && hash_equals($subscriber->verification_token, hash('sha256', $token)),
            403,
        );

        $subscriber->update([
            'verified_at' => now(),
            'verification_token' => null,
            'unsubscribed_at' => null,
        ]);

        return redirect()->route('home')->with('newsletter_success', 'You\'re subscribed. Thanks for confirming!');
    }
}

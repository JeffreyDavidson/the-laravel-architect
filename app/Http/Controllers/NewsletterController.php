<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmNewsletterSubscription;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;

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

    public function showConfirmation(Request $request, Subscriber $subscriber, string $token): View
    {
        $this->ensureValidConfirmationToken($subscriber, $token);

        return view('newsletter.confirm', [
            'actionUrl' => $request->fullUrl(),
            'subscriber' => $subscriber,
        ]);
    }

    public function confirm(Subscriber $subscriber, string $token): RedirectResponse
    {
        $this->ensureValidConfirmationToken($subscriber, $token);

        $subscriber->update([
            'verified_at' => now(),
            'verification_token' => null,
            'unsubscribed_at' => null,
        ]);

        return redirect()->route('home')->with('newsletter_success', 'You\'re subscribed. Thanks for confirming!');
    }

    public function showUnsubscribe(Request $request, Subscriber $subscriber): View
    {
        return view('newsletter.unsubscribe', [
            'actionUrl' => $request->fullUrl(),
            'subscriber' => $subscriber,
        ]);
    }

    public function unsubscribe(Subscriber $subscriber): RedirectResponse
    {
        $subscriber->update([
            'verification_token' => null,
            'unsubscribed_at' => now(),
        ]);

        return redirect()->route('home')->with('newsletter_success', 'You have been unsubscribed.');
    }

    private function ensureValidConfirmationToken(Subscriber $subscriber, string $token): void
    {
        abort_unless(
            $subscriber->verification_token
                && hash_equals($subscriber->verification_token, hash('sha256', $token)),
            403,
        );
    }
}

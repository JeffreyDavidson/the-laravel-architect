<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscribeNewsletterRequest;
use App\Mail\ConfirmNewsletterSubscription;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class SubscribeNewsletterController extends Controller
{
    public function __invoke(SubscribeNewsletterRequest $request): RedirectResponse
    {
        if ($request->filled('website')) {
            return back()->with('newsletter_success', 'Check your email to confirm your subscription.');
        }

        $email = $request->safe()->string('email')->lower()->toString();

        $subscriber = Subscriber::query()->firstOrNew(['email' => $email]);

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

        Mail::to($subscriber->email)->queue(new ConfirmNewsletterSubscription($confirmationUrl));

        return back()->with('newsletter_success', 'Check your email to confirm your subscription.');
    }
}

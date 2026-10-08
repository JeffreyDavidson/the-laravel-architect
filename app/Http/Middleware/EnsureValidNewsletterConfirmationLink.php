<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Subscriber;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets a newsletter confirmation request through only with a valid, unexpired
 * signature and the subscriber's current token. Any other link, including one
 * already used, goes back to the signup form with the same message, so the
 * response never reveals whether the subscriber or token exists.
 */
final class EnsureValidNewsletterConfirmationLink
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $subscriber = $request->route('subscriber');
        $token = $request->route('token');

        if (! $request->hasValidSignature()) {
            return self::redirectToSignupForm();
        }

        if (
            ! $subscriber instanceof Subscriber
            || ! is_string($token)
            || ! $subscriber->hasConfirmationToken($token)
        ) {
            return self::redirectToSignupForm();
        }

        return $next($request);
    }

    /**
     * Also used when the link's subscriber no longer exists.
     */
    public static function redirectToSignupForm(): RedirectResponse
    {
        return redirect()->route('home')
            ->withFragment('newsletter-form')
            ->withErrors(['email' => 'This confirmation link has expired or has already been used. If you already confirmed, you’re subscribed. Otherwise, sign up again below.']);
    }
}

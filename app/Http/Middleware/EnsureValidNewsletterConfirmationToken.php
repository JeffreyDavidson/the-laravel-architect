<?php

namespace App\Http\Middleware;

use App\Models\Subscriber;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidNewsletterConfirmationToken
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $subscriber = $request->route('subscriber');
        $token = $request->route('token');

        abort_unless(
            $subscriber instanceof Subscriber
                && is_string($token)
                && $subscriber->verification_token
                && hash_equals($subscriber->verification_token, hash('sha256', $token)),
            403,
        );

        return $next($request);
    }
}

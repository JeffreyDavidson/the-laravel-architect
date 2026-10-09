<?php

declare(strict_types=1);

return [

    'health' => [

        'runtime' => [

            /*
             * How old the scheduler and queue worker heartbeats may be, in seconds, before the
             * health check fails. Must be at least 60.
             */
            'max_age_seconds' => (int) env('RUNTIME_HEALTH_MAX_AGE', 300),

        ],

    ],

    'monitoring' => [

        /*
         * Strip personal data, secrets, query values and URLs down to safe shapes before Sentry
         * and Nightwatch send anything. Each is wired only when the app has it installed.
         */
        'redact' => true,

    ],

    /*
     * The AddSecurityHeaders middleware. Turnstile and the local Vite dev server are always
     * allowed; list each site's own third-party hosts here.
     */
    'security_headers' => [

        'csp' => [
            'script_src' => [],
            'connect_src' => [],
            'frame_src' => ['https://www.youtube-nocookie.com', 'https://share.transistor.fm'],
            'img_src' => [],
            'media_src' => [],
        ],

        // Paths served by the admin panel: no script nonce, and never cached or indexed.
        'admin_paths' => ['admin'],

        // Path patterns (as for $request->is()) that must never be cached or indexed.
        'noindex_paths' => ['preview', 'preview/*'],

        // Sent on HTTPS responses only. An empty string turns it off.
        'hsts' => 'max-age=31536000; includeSubDomains',

    ],

    /*
     * Cloudflare Turnstile. The env names match the ones both apps used before the package.
     * Verification fails closed: with no secret key or no allowed hostnames, every token is
     * rejected.
     */
    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
        'siteverify_url' => env('TURNSTILE_SITEVERIFY_URL', 'https://challenges.cloudflare.com/turnstile/v0/siteverify'),
        'allowed_hostnames' => array_values(array_filter(array_map(
            trim(...),
            explode(',', (string) env('TURNSTILE_ALLOWED_HOSTNAMES', '')),
        ))),
        'contact_action' => env('TURNSTILE_CONTACT_ACTION', 'contact-form'),
        'newsletter_action' => env('TURNSTILE_NEWSLETTER_ACTION', 'newsletter'),
    ],

];

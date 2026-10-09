<?php

declare(strict_types=1);

use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Subscriber;

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

    'media' => [

        /*
         * The widths, in pixels, of the responsive WebP variants generated for every stored
         * image. Keep it ascending.
         */
        'responsive_widths' => [640, 1280],

        /*
         * The image columns the media:repair-responsive-images and media:verify-responsive-images
         * commands cover, in the order they report them.
         */
        'responsive_images' => [
            ['model' => Project::class, 'column' => 'featured_image_path', 'label' => 'project'],
            ['model' => Post::class, 'column' => 'featured_image_path', 'label' => 'post'],
            ['model' => Podcast::class, 'column' => 'cover_image_path', 'label' => 'podcast'],
        ],

    ],

    'newsletter' => [

        // The app's newsletter models; the package refuses to run without them.
        'models' => [
            'subscriber' => Subscriber::class,
            'issue' => NewsletterIssue::class,
            'delivery' => NewsletterDelivery::class,
        ],

        // Where an unusable confirmation link sends the reader.
        'signup_form' => ['route' => 'home', 'fragment' => 'newsletter-form', 'error_bag' => 'default'],

        // An editor's test send goes to these addresses (the same address as mail.contact_to).
        'test_recipients' => [env('MAIL_CONTACT_TO', env('MAIL_FROM_ADDRESS', 'hello@example.com'))],

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

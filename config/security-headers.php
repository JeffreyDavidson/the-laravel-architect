<?php

declare(strict_types=1);

/*
 * The AddSecurityHeaders middleware. Turnstile and the local Vite dev server are always allowed;
 * list the site's own third-party hosts here.
 */
return [

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

];

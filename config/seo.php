<?php

use RalphJSmit\Laravel\SEO\Models\SEO;

return [
    'model' => SEO::class,

    'site_name' => 'The Laravel Architect',

    'sitemap' => null,

    'canonical_link' => true,

    'robots' => [
        'default' => 'max-snippet:-1,max-image-preview:large,max-video-preview:-1',
        'force_default' => false,
    ],

    'favicon' => '/images/favicon-32x32.png',

    'title' => [
        'infer_title_from_url' => true,
        'suffix' => ' — Jeffrey Davidson',
        'homepage_title' => 'The Laravel Architect — Jeffrey Davidson',
    ],

    'description' => [
        'fallback' => 'Blog, portfolio, and insights from Jeffrey Davidson — Laravel developer, content creator, and software architect.',
    ],

    'image' => [
        'fallback' => '/images/logo-color-black-bg.png',
    ],

    'author' => [
        'fallback' => 'Jeffrey Davidson',
    ],

    'twitter' => [
        '@username' => 'thelaravelarch',
    ],
];

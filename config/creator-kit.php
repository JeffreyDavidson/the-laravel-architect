<?php

declare(strict_types=1);

use App\Models\ContactInquiry;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Subscriber;

return [

    'contact' => [

        // The app's contact inquiry model; the package refuses to run without it.
        'model' => ContactInquiry::class,

        // Who gets the new-inquiry email (the same address as mail.contact_to).
        'notify' => [env('MAIL_CONTACT_TO', env('MAIL_FROM_ADDRESS', 'hello@example.com'))],

        // Days before model:prune deletes an inquiry; null keeps them.
        'retention_days' => (int) env('CONTACT_INQUIRY_RETENTION_DAYS', 180),

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

];

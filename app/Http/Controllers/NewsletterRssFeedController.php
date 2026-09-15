<?php

namespace App\Http\Controllers;

use App\Actions\GenerateNewsletterRssFeed;
use Illuminate\Http\Response;

class NewsletterRssFeedController
{
    public function __invoke(GenerateNewsletterRssFeed $generateNewsletterRssFeed): Response
    {
        return response($generateNewsletterRssFeed->handle())
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}

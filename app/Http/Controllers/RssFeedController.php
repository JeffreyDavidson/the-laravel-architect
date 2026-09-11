<?php

namespace App\Http\Controllers;

use App\Actions\GenerateRssFeed;
use Illuminate\Http\Response;

class RssFeedController
{
    public function __invoke(GenerateRssFeed $generateRssFeed): Response
    {
        return response($generateRssFeed->handle())
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}

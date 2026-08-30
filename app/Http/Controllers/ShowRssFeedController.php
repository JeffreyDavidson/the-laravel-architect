<?php

namespace App\Http\Controllers;

use App\Actions\GenerateRssFeed;
use Illuminate\Http\Response;

class ShowRssFeedController extends Controller
{
    public function __invoke(GenerateRssFeed $generateRssFeed): Response
    {
        return response($generateRssFeed())
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}

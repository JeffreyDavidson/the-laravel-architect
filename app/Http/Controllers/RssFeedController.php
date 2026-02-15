<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;

class RssFeedController extends Controller
{
    public function __invoke(): Response
    {
        $posts = Post::published()
            ->latest('published_at')
            ->take(20)
            ->get();

        $content = view('rss.feed', compact('posts'))->render();

        return response($content)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}

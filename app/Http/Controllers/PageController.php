<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use App\Services\YouTubeService;

class PageController extends Controller
{
    public function home()
    {
        $latestPosts = Post::published()
            ->with(['category', 'tags'])
            ->latest('published_at')
            ->take(3)
            ->get();

        $featuredProjects = Project::published()
            ->featured()
            ->orderBy('sort_order')
            ->take(4)
            ->get();

        $youtubeSubscribers = YouTubeService::subscriberCount();

        return view('pages.home', compact('latestPosts', 'featuredProjects', 'youtubeSubscribers'));
    }

    public function about()
    {
        return view('pages.about');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function uses()
    {
        return view('pages.uses');
    }
}

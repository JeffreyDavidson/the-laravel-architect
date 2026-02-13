<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;

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

        return view('pages.home', compact('latestPosts', 'featuredProjects'));
    }

    public function about()
    {
        return view('pages.about');
    }

    public function contact()
    {
        return view('pages.contact');
    }
}

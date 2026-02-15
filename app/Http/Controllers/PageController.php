<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use App\Models\Testimonial;
use App\Services\YouTubeService;
use RalphJSmit\Laravel\SEO\Support\SEOData;

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

        $testimonials = Testimonial::approved()
            ->orderBy('sort_order')
            ->latest()
            ->get();

        seo()
            ->title('The Laravel Architect — Jeffrey Davidson')
            ->description('Blog, portfolio, and insights from Jeffrey Davidson — Laravel developer, content creator, and software architect based in Florida.');

        return view('pages.home', compact('latestPosts', 'featuredProjects', 'youtubeSubscribers', 'testimonials'));
    }

    public function about()
    {
        seo()
            ->title('About')
            ->description('Meet Jeffrey Davidson — 15+ years of PHP experience, Laravel architect, podcaster, and dad. Building clean, maintainable applications and sharing the journey.');

        return view('pages.about');
    }

    public function contact()
    {
        seo()
            ->title('Contact')
            ->description('Get in touch with Jeffrey Davidson for freelance Laravel development, consulting, legacy modernization, or just to say hello.');

        return view('pages.contact');
    }

    public function uses()
    {
        seo()
            ->title('Uses')
            ->description('The tools, hardware, and software Jeffrey Davidson uses for Laravel development, content creation, and everyday work.');

        return view('pages.uses');
    }
}

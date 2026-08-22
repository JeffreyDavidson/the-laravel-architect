<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use App\Models\Testimonial;
use App\Models\Video;
use App\Services\YouTubeService;
use Illuminate\Contracts\View\View;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class PageController extends Controller
{
    public function home(): View
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
        $latestYouTubeVideos = Video::published()
            ->latest('published_at')
            ->take(3)
            ->get();

        $testimonials = Testimonial::approved()
            ->orderBy('sort_order')
            ->latest()
            ->get();

        $publishedPostCount = Post::published()->count();
        $publishedProjectCount = Project::published()->count();

        seo()->for(new SEOData(
            title: 'The Laravel Architect — Jeffrey Davidson',
            description: 'Blog, portfolio, and insights from Jeffrey Davidson — Laravel developer, content creator, and software architect based in Florida.',
        ));

        return view('pages.home', compact(
            'latestPosts',
            'featuredProjects',
            'youtubeSubscribers',
            'latestYouTubeVideos',
            'testimonials',
            'publishedPostCount',
            'publishedProjectCount',
        ));
    }

    public function about(): View
    {
        seo()->for(new SEOData(
            title: 'About',
            description: 'Meet Jeffrey Davidson — 15+ years of PHP experience, Laravel architect, podcaster, and dad. Building clean, maintainable applications and sharing the journey.',
        ));

        return view('pages.about');
    }

    public function contact(): View
    {
        seo()->for(new SEOData(
            title: 'Contact',
            description: 'Get in touch with Jeffrey Davidson for freelance Laravel development, consulting, legacy modernization, or just to say hello.',
        ));

        return view('pages.contact');
    }

    public function privacy(): View
    {
        seo()->for(new SEOData(
            title: 'Privacy',
            description: 'How The Laravel Architect handles contact messages, newsletter subscriptions, testimonials, analytics, and essential site data.',
        ));

        return view('pages.privacy');
    }

    public function uses(): View
    {
        seo()->for(new SEOData(
            title: 'Uses',
            description: 'The tools, hardware, and software Jeffrey Davidson uses for Laravel development, content creation, and everyday work.',
        ));

        return view('pages.uses');
    }
}

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

        $upcomingVideos = [
            [
                'variant' => 'testing',
                'thumbnail' => '/images/yt-thumb-testing.png',
                'imageAlt' => 'Testing Like You Mean It',
                'badge' => 'Testing',
                'previewTitle' => ['Testing Like', 'You Mean It'],
                'previewSubtitle' => '3 Suites, Zero Excuses',
                'duration' => '12:34',
                'title' => 'Testing Like You Mean It: 3 Suites, Zero Excuses',
                'meta' => 'The Laravel Architect · Coming Mar 2',
            ],
            [
                'variant' => 'saas',
                'thumbnail' => '/images/yt-thumb-saas.png',
                'imageAlt' => 'Build a SaaS from Scratch',
                'badge' => 'Full Build',
                'previewTitle' => ['Build a SaaS', 'from Scratch'],
                'previewSubtitle' => 'Laravel & Filament',
                'duration' => '18:47',
                'title' => 'Build a SaaS from Scratch with Laravel & Filament',
                'meta' => 'The Laravel Architect · Coming Mar 9',
            ],
            [
                'variant' => 'codeigniter',
                'thumbnail' => '/images/yt-thumb-codeigniter.png',
                'imageAlt' => 'Why I Left CodeIgniter',
                'badge' => 'Story',
                'previewTitle' => ['Why I Left', 'CodeIgniter'],
                'previewSubtitle' => 'And Never Looked Back',
                'duration' => '24:12',
                'title' => 'Why I Left CodeIgniter (And Never Looked Back)',
                'meta' => 'The Laravel Architect · Coming Mar 16',
            ],
        ];

        $testimonials = Testimonial::approved()
            ->orderBy('sort_order')
            ->latest()
            ->get();

        seo()->for(new SEOData(
            title: 'The Laravel Architect — Jeffrey Davidson',
            description: 'Blog, portfolio, and insights from Jeffrey Davidson — Laravel developer, content creator, and software architect based in Florida.',
        ));

        return view('pages.home', compact('latestPosts', 'featuredProjects', 'youtubeSubscribers', 'upcomingVideos', 'testimonials'));
    }

    public function about()
    {
        seo()->for(new SEOData(
            title: 'About',
            description: 'Meet Jeffrey Davidson — 15+ years of PHP experience, Laravel architect, podcaster, and dad. Building clean, maintainable applications and sharing the journey.',
        ));

        return view('pages.about');
    }

    public function contact()
    {
        seo()->for(new SEOData(
            title: 'Contact',
            description: 'Get in touch with Jeffrey Davidson for freelance Laravel development, consulting, legacy modernization, or just to say hello.',
        ));

        return view('pages.contact');
    }

    public function uses()
    {
        seo()->for(new SEOData(
            title: 'Uses',
            description: 'The tools, hardware, and software Jeffrey Davidson uses for Laravel development, content creation, and everyday work.',
        ));

        return view('pages.uses');
    }
}

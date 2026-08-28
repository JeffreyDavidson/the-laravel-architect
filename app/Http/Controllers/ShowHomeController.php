<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use App\Models\Testimonial;
use App\Models\Video;
use App\Services\YouTubeService;
use Illuminate\Contracts\View\View;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class ShowHomeController extends Controller
{
    public function __invoke(): View
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

        $seoSource = new SEOData(
            title: 'The Laravel Architect — Jeffrey Davidson',
            description: 'Blog, portfolio, and insights from Jeffrey Davidson — Laravel developer, content creator, and software architect based in Florida.',
        );

        return view('pages.home', [
            'latestPosts' => $latestPosts,
            'featuredProjects' => $featuredProjects,
            'youtubeSubscribers' => $youtubeSubscribers,
            'latestYouTubeVideos' => $latestYouTubeVideos,
            'testimonials' => $testimonials,
            'publishedPostCount' => $publishedPostCount,
            'publishedProjectCount' => $publishedProjectCount,
            'seoSource' => $seoSource,
        ]);
    }
}

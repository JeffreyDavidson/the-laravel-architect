<?php

namespace App\ViewModels;

use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Video;
use App\Services\YouTubeService;
use Illuminate\Database\Eloquent\Collection;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class HomeViewModel
{
    /**
     * @return array{
     *     latestPosts: Collection<int, Post>,
     *     featuredProjects: Collection<int, Project>,
     *     podcast: Podcast|null,
     *     youtubeSubscribers: int,
     *     latestYouTubeVideos: Collection<int, Video>,
     *     publishedPostCount: int,
     *     publishedProjectCount: int,
     *     seoSource: SEOData,
     * }
     */
    public function data(): array
    {
        return [
            'latestPosts' => Post::published()
                ->with(['category', 'tags'])
                ->latest('published_at')
                ->take(3)
                ->get(),
            'featuredProjects' => Project::published()
                ->portfolio()
                ->featured()
                ->orderBy('sort_order')
                ->take(4)
                ->get(),
            'podcast' => Podcast::active()
                ->orderBy('sort_order')
                ->first(),
            'youtubeSubscribers' => YouTubeService::subscriberCount(),
            'latestYouTubeVideos' => Video::published()
                ->latest('published_at')
                ->take(3)
                ->get(),
            'publishedPostCount' => Post::published()->count(),
            'publishedProjectCount' => Project::published()->portfolio()->count(),
            'seoSource' => new SEOData(
                title: 'The Laravel Architect — Jeffrey Davidson',
                description: 'Blog, portfolio, and insights from Jeffrey Davidson — Laravel developer, content creator, and software architect based in Florida.',
            ),
        ];
    }
}

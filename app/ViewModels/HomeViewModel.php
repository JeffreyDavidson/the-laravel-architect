<?php

namespace App\ViewModels;

use App\Enums\SocialPlatform;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Video;
use App\Queries\SocialProfilesQuery;
use Illuminate\Database\Eloquent\Collection;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class HomeViewModel
{
    public function __construct(private SocialProfilesQuery $socialProfilesQuery) {}

    /**
     * @return array{
     *     latestPosts: Collection<int, Post>,
     *     featuredPost: Post|null,
     *     featuredProjects: Collection<int, Project>,
     *     podcast: Podcast|null,
     *     latestYouTubeVideos: Collection<int, Video>,
     *     youtubeProfileUrl: string|null,
     *     publishedPostCount: int,
     *     publishedProjectCount: int,
     *     seoSource: SEOData,
     * }
     */
    public function data(): array
    {
        $latestPosts = Post::published()
            ->with(['category', 'tags'])
            ->latest('published_at')
            ->take(3)
            ->get();

        return [
            'latestPosts' => $latestPosts,
            'featuredPost' => $latestPosts->first(),
            'featuredProjects' => Project::published()
                ->featured()
                ->orderBy('sort_order')
                ->take(4)
                ->get(),
            'podcast' => Podcast::active()
                ->orderBy('sort_order')
                ->first(),
            'latestYouTubeVideos' => Video::published()
                ->latest('published_at')
                ->take(3)
                ->get(),
            'youtubeProfileUrl' => $this->socialProfilesQuery->enabledUrlFor(SocialPlatform::YouTube),
            'publishedPostCount' => Post::published()->count(),
            'publishedProjectCount' => Project::published()->count(),
            'seoSource' => new SEOData(
                title: 'The Laravel Architect — Jeffrey Davidson',
                description: 'Blog, portfolio, and insights from Jeffrey Davidson — Laravel developer, content creator, and software architect based in Florida.',
            ),
        ];
    }
}

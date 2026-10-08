<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Contracts\PageViewModel;
use App\Data\PageMeta;
use App\Enums\SocialPlatform;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Video;
use App\Queries\SocialProfilesQuery;
use Illuminate\Database\Eloquent\Collection;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final readonly class HomeViewModel implements PageViewModel
{
    public function __construct(
        private SocialProfilesQuery $socialProfilesQuery,
        private SiteStructuredData $site,
    ) {}

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
     *     pageMeta: PageMeta,
     * }
     */
    public function data(): array
    {
        $latestPosts = Post::query()->published()
            ->with(['category', 'tags'])
            ->latest('published_at')
            ->take(3)
            ->get();

        return [
            'latestPosts' => $latestPosts,
            'featuredPost' => $latestPosts->first(),
            'featuredProjects' => Project::query()->published()
                ->featured()
                ->orderBy('sort_order')
                ->take(4)
                ->get(),
            'podcast' => Podcast::query()->active()
                ->orderBy('sort_order')
                ->first(),
            'latestYouTubeVideos' => Video::query()->published()
                ->latest('published_at')
                ->take(3)
                ->get(),
            'youtubeProfileUrl' => $this->socialProfilesQuery->enabledUrlFor(SocialPlatform::YouTube),
            'publishedPostCount' => Post::query()->published()
                ->count(),
            'publishedProjectCount' => Project::query()->published()
                ->count(),
            'pageMeta' => new PageMeta(
                seo: new SEOData(
                    title: 'The Laravel Architect — Jeffrey Davidson',
                    description: 'Blog, portfolio, and insights from Jeffrey Davidson — Laravel developer, content creator, and software architect based in Florida.',
                ),
                // The home page is the site's root, so it has no breadcrumb trail.
                structuredData: [$this->site->page('WebPage', 'The Laravel Architect', route('home'))],
            ),
        ];
    }
}

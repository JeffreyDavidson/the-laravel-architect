<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Filament\Resources\Podcasts\PodcastResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Videos\VideoResource;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Video;
use App\Support\Content\ContentReadiness;
use Filament\Widgets\Widget;

class ContentReadinessWidget extends Widget
{
    #[\Override]
    protected string $view = 'filament.widgets.content-readiness-widget';

    #[\Override]
    protected int|string|array $columnSpan = 'full';

    #[\Override]
    protected static ?int $sort = -2;

    /**
     * @return array{items: list<array{label: string, description: string, count: int, url: string}>, outstandingCount: int}
     */
    protected function getViewData(): array
    {
        $posts = Post::query()
            ->with('seo')
            ->withCount('tags')
            ->lazyById(100);
        $projects = Project::query()
            ->with('seo')
            ->withCount('tags')
            ->lazyById(100);
        $podcasts = Podcast::query()
            ->active()
            ->with('seo')
            ->lazyById(100);
        $episodes = Episode::query()
            ->with(['seo', 'podcast'])
            ->withCount('tags')
            ->lazyById(100);
        $newsletterIssues = NewsletterIssue::query()
            ->with('seo')
            ->lazyById(100);
        $videos = Video::query()->lazyById(100);

        $items = array_values(array_filter([
            [
                'label' => 'Project previews',
                'description' => 'Add an optimized featured image to each project.',
                'count' => $this->missingCount($projects, 'featured_image'),
                'url' => ProjectResource::getUrl('index'),
            ],
            [
                'label' => 'Project stories',
                'description' => 'Finish the case study for each project.',
                'count' => $this->missingCount($projects, 'case_study'),
                'url' => ProjectResource::getUrl('index'),
            ],
            [
                'label' => 'Podcast links',
                'description' => 'Add at least one place listeners can subscribe.',
                'count' => $this->missingCount($podcasts, 'subscribe_link'),
                'url' => PodcastResource::getUrl('index'),
            ],
            [
                'label' => 'Episode details',
                'description' => 'Add a playable episode source and show notes.',
                'count' => $this->missingAnyCount($episodes, ['episode_media', 'show_notes']),
                'url' => EpisodeResource::getUrl('index'),
            ],
            [
                'label' => 'Post content',
                'description' => 'Add an excerpt, image, and SEO description to each post.',
                'count' => $this->missingAnyCount($posts, ['excerpt', 'featured_image', 'seo_description']),
                'url' => PostResource::getUrl('index'),
            ],
            [
                'label' => 'Newsletter issues',
                'description' => 'Add an excerpt and SEO description before sending an issue.',
                'count' => $this->missingAnyCount($newsletterIssues, ['excerpt', 'seo_description']),
                'url' => NewsletterIssueResource::getUrl('index'),
            ],
            [
                'label' => 'Video metadata',
                'description' => 'Complete the description, thumbnail, duration, and sync data.',
                'count' => $this->missingAnyCount($videos, ['description', 'thumbnail', 'duration', 'synced']),
                'url' => VideoResource::getUrl('index'),
            ],
        ], fn (array $item): bool => $item['count'] > 0));

        $outstandingCount = 0;

        foreach ($items as $item) {
            $outstandingCount += $item['count'];
        }

        return [
            'items' => array_slice($items, 0, 4),
            'outstandingCount' => $outstandingCount,
        ];
    }

    /**
     * @param  iterable<Post|Project|Podcast|Episode|NewsletterIssue|Video>  $records
     */
    private function missingCount(iterable $records, string $check): int
    {
        return $this->missingAnyCount($records, [$check]);
    }

    /**
     * @param  iterable<Post|Project|Podcast|Episode|NewsletterIssue|Video>  $records
     * @param  list<string>  $checks
     */
    private function missingAnyCount(iterable $records, array $checks): int
    {
        $missing = 0;

        foreach ($records as $record) {
            $readinessChecks = new ContentReadiness($record)->checks();

            if (array_any($checks, fn (string $check): bool => ! ($readinessChecks[$check]['complete'] ?? false))) {
                $missing++;
            }
        }

        return $missing;
    }
}

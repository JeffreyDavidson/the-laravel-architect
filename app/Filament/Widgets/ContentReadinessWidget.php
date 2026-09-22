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
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class ContentReadinessWidget extends Widget
{
    private const int CACHE_SECONDS = 60;

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
        /** @var array{items: list<array{label: string, description: string, count: int, url: string}>, outstandingCount: int} $cached */
        $cached = Cache::remember(
            'filament.dashboard.content-readiness',
            now()->addSeconds(self::CACHE_SECONDS),
            fn (): array => $this->buildViewData(),
        );

        return $cached;
    }

    /**
     * @return array{items: list<array{label: string, description: string, count: int, url: string}>, outstandingCount: int}
     */
    private function buildViewData(): array
    {
        $items = array_values(array_filter([
            [
                'label' => 'Project previews',
                'description' => 'Add an optimized featured image to each project.',
                'count' => Project::query()->where(fn (Builder $query) => $query->whereNull('featured_image_path')->orWhere('featured_image_path', ''))->count(),
                'url' => ProjectResource::getUrl('index'),
            ],
            [
                'label' => 'Project stories',
                'description' => 'Finish the case study for each project.',
                'count' => Project::query()->where(fn (Builder $query) => $query->whereNull('content')->orWhere('content', ''))->count(),
                'url' => ProjectResource::getUrl('index'),
            ],
            [
                'label' => 'Podcast links',
                'description' => 'Add at least one place listeners can subscribe.',
                'count' => Podcast::query()
                    ->active()
                    ->where(function (Builder $query): void {
                        foreach (['apple_url', 'spotify_url', 'rss_url', 'youtube_url'] as $column) {
                            $query->where(function (Builder $query) use ($column): void {
                                $query->whereNull($column)->orWhere($column, '');
                            });
                        }
                    })
                    ->count(),
                'url' => PodcastResource::getUrl('index'),
            ],
            [
                'label' => 'Episode details',
                'description' => 'Add a playable episode source and show notes.',
                'count' => $this->missingEpisodeDetailsCount(),
                'url' => EpisodeResource::getUrl('index'),
            ],
            [
                'label' => 'Post content',
                'description' => 'Add an excerpt, image, and SEO description to each post.',
                'count' => Post::query()->where(fn (Builder $query) => $query->whereNull('excerpt')->orWhere('excerpt', '')->orWhereNull('featured_image_path')->orWhere('featured_image_path', ''))->count(),
                'url' => PostResource::getUrl('index'),
            ],
            [
                'label' => 'Newsletter issues',
                'description' => 'Add an excerpt and SEO description before sending an issue.',
                'count' => NewsletterIssue::query()->where(fn (Builder $query) => $query->whereNull('excerpt')->orWhere('excerpt', ''))->count(),
                'url' => NewsletterIssueResource::getUrl('index'),
            ],
            [
                'label' => 'Video metadata',
                'description' => 'Complete the description, thumbnail, duration, and sync data.',
                'count' => Video::query()->where(fn (Builder $query) => $query->whereNull('description')->orWhere('description', '')->orWhereNull('thumbnail_url')->orWhere('thumbnail_url', '')->orWhereNull('duration')->orWhere('duration', '')->orWhereNull('synced_at'))->count(),
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

    private function missingEpisodeDetailsCount(): int
    {
        $missing = 0;

        foreach (Episode::query()->get(['audio_url', 'audio_path', 'embed_url', 'youtube_url', 'show_notes']) as $episode) {
            $hasMedia = filled($episode->audio_url)
                || filled($episode->audio_path)
                || $episode->publicEmbedUrl() !== null
                || filled($episode->youtube_url);

            if (! $hasMedia || blank($episode->show_notes)) {
                $missing++;
            }
        }

        return $missing;
    }
}

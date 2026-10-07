<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\PublishStatus;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\Project;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * @phpstan-type Activity array{kind: string, label: string, status: string, time: string, timestamp: Carbon|null, url: string}
 */
class RecentActivityWidget extends Widget
{
    /**
     * Each content model with the label and admin resource used for its activity rows.
     */
    private const array SOURCES = [
        Post::class => ['Post', PostResource::class],
        Episode::class => ['Episode', EpisodeResource::class],
        NewsletterIssue::class => ['Newsletter', NewsletterIssueResource::class],
        Project::class => ['Project', ProjectResource::class],
    ];

    #[\Override]
    protected string $view = 'filament.widgets.recent-activity-widget';

    #[\Override]
    protected int|string|array $columnSpan = 1;

    #[\Override]
    protected static ?int $sort = -4;

    /**
     * @return array{activities: Collection<int, Activity>}
     */
    protected function getViewData(): array
    {
        /** @var Collection<int, Activity> $activities */
        $activities = collect();

        foreach (self::SOURCES as $model => [$kind, $resource]) {
            $activities = $activities->concat($this->recentActivities($model::query(), $kind, $resource));
        }

        return [
            'activities' => $activities->sortByDesc('timestamp')
                ->take(5)
                ->values(),
        ];
    }

    /**
     * @param  Builder<Post>|Builder<Episode>|Builder<NewsletterIssue>|Builder<Project>  $query
     * @param  class-string<PostResource|EpisodeResource|NewsletterIssueResource|ProjectResource>  $resource
     * @return Collection<int, Activity>
     */
    private function recentActivities(Builder $query, string $kind, string $resource): Collection
    {
        return $query->latest('updated_at')
            ->take(5)
            ->get()
            ->map(fn (Post|Episode|NewsletterIssue|Project $record): array => $this->activity(
                kind: $kind,
                label: $record->title,
                status: $record->publishStatus(),
                updatedAt: $record->updated_at,
                url: $resource::getUrl('edit', ['record' => $record]),
            ));
    }

    /**
     * @return Activity
     */
    private function activity(
        string $kind,
        string $label,
        PublishStatus $status,
        ?Carbon $updatedAt,
        string $url,
    ): array {
        return [
            'kind' => $kind,
            'label' => $label,
            'status' => $status->label(),
            'time' => $updatedAt?->diffForHumans() ?? 'Unknown',
            'timestamp' => $updatedAt,
            'url' => $url,
        ];
    }
}

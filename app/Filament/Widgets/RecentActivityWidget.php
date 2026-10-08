<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\Project;
use App\Queries\RecentlyEditedContentQuery;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

/**
 * @phpstan-type Activity array{kind: string, label: string, status: string, time: string, timestamp: Carbon|null, url: string}
 */
final class RecentActivityWidget extends Widget
{
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
        return [
            'activities' => app(RecentlyEditedContentQuery::class)
                ->get(5)
                ->map(fn (Post|Episode|NewsletterIssue|Project $record): array => $this->activity($record)),
        ];
    }

    /**
     * Map a record to its activity row, labelled and linked to the record's admin resource.
     *
     * @return Activity
     */
    private function activity(Post|Episode|NewsletterIssue|Project $record): array
    {
        [$kind, $resource] = match (true) {
            $record instanceof Post => ['Post', PostResource::class],
            $record instanceof Episode => ['Episode', EpisodeResource::class],
            $record instanceof NewsletterIssue => ['Newsletter', NewsletterIssueResource::class],
            $record instanceof Project => ['Project', ProjectResource::class],
        };
        $updatedAt = $record->updated_at;

        return [
            'kind' => $kind,
            'label' => $record->title,
            'status' => $record
                ->publishStatus()
                ->getLabel(),
            'time' => $updatedAt?->diffForHumans() ?? 'Unknown',
            'timestamp' => $updatedAt,
            'url' => $resource::getUrl('edit', ['record' => $record]),
        ];
    }
}

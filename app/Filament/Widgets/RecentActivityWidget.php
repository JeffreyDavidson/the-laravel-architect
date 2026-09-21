<?php

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
use Illuminate\Support\Collection;

class RecentActivityWidget extends Widget
{
    #[\Override]
    protected string $view = 'filament.widgets.recent-activity-widget';

    #[\Override]
    protected int|string|array $columnSpan = 1;

    #[\Override]
    protected static ?int $sort = -4;

    /**
     * @return array{activities: Collection<int, array{kind: string, label: string, status: string, time: string, timestamp: Carbon|null, url: string}>}
     */
    protected function getViewData(): array
    {
        $activities = Post::query()->latest('updated_at')->take(5)->get()->map(
            fn (Post $post): array => $this->activity(
                kind: 'Post',
                label: $post->title,
                status: $post->publishStatus(),
                updatedAt: $post->updated_at,
                url: PostResource::getUrl('edit', ['record' => $post]),
            ),
        )->concat(Episode::query()->latest('updated_at')->take(5)->get()->map(
            fn (Episode $episode): array => $this->activity(
                kind: 'Episode',
                label: $episode->title,
                status: $episode->publishStatus(),
                updatedAt: $episode->updated_at,
                url: EpisodeResource::getUrl('edit', ['record' => $episode]),
            ),
        ))->concat(NewsletterIssue::query()->latest('updated_at')->take(5)->get()->map(
            fn (NewsletterIssue $issue): array => $this->activity(
                kind: 'Newsletter',
                label: $issue->title,
                status: $issue->publishStatus(),
                updatedAt: $issue->updated_at,
                url: NewsletterIssueResource::getUrl('edit', ['record' => $issue]),
            ),
        ))->concat(Project::query()->latest('updated_at')->take(5)->get()->map(
            fn (Project $project): array => $this->activity(
                kind: 'Project',
                label: $project->title,
                status: $project->publishStatus(),
                updatedAt: $project->updated_at,
                url: ProjectResource::getUrl('edit', ['record' => $project]),
            ),
        ));

        return [
            'activities' => $activities->sortByDesc('timestamp')->take(5)->values(),
        ];
    }

    /**
     * @return array{kind: string, label: string, status: string, time: string, timestamp: Carbon|null, url: string}
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

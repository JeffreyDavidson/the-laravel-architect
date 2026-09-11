<?php

namespace App\Filament\Widgets;

use App\Enums\PublishStatus;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Widgets\Widget;

class RecentActivityWidget extends Widget
{
    protected string $view = 'filament.widgets.recent-activity-widget';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = -4;

    protected function getViewData(): array
    {
        $activities = collect();

        Post::latest('updated_at')->take(3)->get()->each(function (Post $post) use ($activities) {
            $updatedAt = $post->updated_at;

            $activities->push([
                'icon' => '📝',
                'label' => $post->title,
                'meta' => $post->publishStatus() === PublishStatus::Published ? 'Published' : 'Draft',
                'time' => $updatedAt?->diffForHumans() ?? 'Unknown',
                'kind' => 'post',
                'url' => PostResource::getUrl('edit', ['record' => $post]),
                'timestamp' => $updatedAt,
            ]);
        });

        return [
            'activities' => $activities->sortByDesc('timestamp')->take(5)->values(),
        ];
    }
}

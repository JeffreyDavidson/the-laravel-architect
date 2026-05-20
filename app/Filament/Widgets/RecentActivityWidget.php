<?php

namespace App\Filament\Widgets;

use App\Enums\PublishStatus;
use App\Models\Post;
use App\Models\Testimonial;
use Filament\Widgets\Widget;

class RecentActivityWidget extends Widget
{
    protected string $view = 'filament.widgets.recent-activity-widget';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = -4;

    protected function getViewData(): array
    {
        $activities = collect();

        Post::latest('updated_at')->take(3)->get()->each(function ($post) use ($activities) {
            $activities->push([
                'icon' => '📝',
                'label' => $post->title,
                'meta' => $post->status === PublishStatus::Published ? 'Published' : 'Draft',
                'time' => $post->updated_at->diffForHumans(),
                'kind' => 'post',
                'timestamp' => $post->updated_at,
            ]);
        });

        Testimonial::latest('created_at')->take(2)->get()->each(function ($testimonial) use ($activities) {
            $statusLabel = match ($testimonial->status) {
                'pending' => 'Pending Review',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                default => $testimonial->status,
            };
            $activities->push([
                'icon' => '💬',
                'label' => 'Testimonial from '.$testimonial->name,
                'meta' => $statusLabel,
                'time' => $testimonial->created_at->diffForHumans(),
                'kind' => 'testimonial',
                'timestamp' => $testimonial->created_at,
            ]);
        });

        return [
            'activities' => $activities->sortByDesc('timestamp')->take(5)->values(),
        ];
    }
}

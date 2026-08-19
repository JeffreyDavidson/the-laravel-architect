<?php

namespace App\Filament\Widgets;

use App\Enums\PublishStatus;
use App\Enums\TestimonialStatus;
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
            $updatedAt = $post->updated_at;

            $activities->push([
                'icon' => '📝',
                'label' => $post->title,
                'meta' => $post->publishStatus() === PublishStatus::Published ? 'Published' : 'Draft',
                'time' => $updatedAt?->diffForHumans() ?? 'Unknown',
                'kind' => 'post',
                'timestamp' => $updatedAt,
            ]);
        });

        Testimonial::latest('created_at')->take(2)->get()->each(function ($testimonial) use ($activities) {
            $createdAt = $testimonial->created_at;
            $statusLabel = match ($testimonial->testimonialStatus()) {
                TestimonialStatus::Pending => 'Pending Review',
                TestimonialStatus::Approved => 'Approved',
                TestimonialStatus::Rejected => 'Rejected',
            };
            $activities->push([
                'icon' => '💬',
                'label' => 'Testimonial from '.$testimonial->name,
                'meta' => $statusLabel,
                'time' => $createdAt?->diffForHumans() ?? 'Unknown',
                'kind' => 'testimonial',
                'timestamp' => $createdAt,
            ]);
        });

        return [
            'activities' => $activities->sortByDesc('timestamp')->take(5)->values(),
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use App\Models\Testimonial;
use Filament\Widgets\Widget;

class RecentActivityWidget extends Widget
{
    protected string $view = 'filament.widgets.recent-activity-widget';

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    protected static ?int $sort = -4;

    protected function getViewData(): array
    {
        $activities = collect();

        Post::latest('updated_at')->take(3)->get()->each(function ($post) use ($activities) {
            $activities->push([
                'icon' => '📝',
                'label' => $post->title,
                'meta' => $post->status === 'published' ? 'Published' : 'Draft',
                'time' => $post->updated_at->diffForHumans(),
                'color' => $post->status === 'published' ? 'text-emerald-400' : 'text-gray-400',
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
                'label' => 'Testimonial from ' . $testimonial->name,
                'meta' => $statusLabel,
                'time' => $testimonial->created_at->diffForHumans(),
                'color' => match ($testimonial->status) {
                    'pending' => 'text-amber-400',
                    'approved' => 'text-emerald-400',
                    'rejected' => 'text-red-400',
                    default => 'text-gray-400',
                },
            ]);
        });

        return [
            'activities' => $activities->sortByDesc('time')->take(5)->values(),
        ];
    }
}

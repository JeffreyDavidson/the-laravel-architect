<?php

namespace App\Filament\Widgets;

use App\Enums\PublishStatus;
use App\Models\Post;
use App\Models\Project;
use App\Models\Subscriber;
use App\Models\Testimonial;
use App\Models\Video;
use Filament\Widgets\Widget;

class WelcomeWidget extends Widget
{
    protected string $view = 'filament.widgets.welcome-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -10;

    /**
     * @return array{
     *     posts: int,
     *     publishedPosts: int,
     *     draftPosts: int,
     *     inReviewPosts: int,
     *     projects: int,
     *     featuredProjects: int,
     *     subscribers: int,
     *     videos: int,
     *     pendingTestimonials: int
     * }
     */
    protected function getViewData(): array
    {
        return [
            'posts' => Post::query()->count(),
            'publishedPosts' => Post::query()->where('status', PublishStatus::Published)->count(),
            'draftPosts' => Post::query()->where('status', PublishStatus::Draft)->count(),
            'inReviewPosts' => Post::query()->where('status', PublishStatus::InReview)->count(),
            'projects' => Project::query()->count(),
            'featuredProjects' => Project::query()->where('is_featured', true)->count(),
            'subscribers' => Subscriber::count(),
            'videos' => Video::count(),
            'pendingTestimonials' => Testimonial::where('status', 'pending')->count(),
        ];
    }
}

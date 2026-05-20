<?php

namespace App\Filament\Widgets;

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

    protected function getViewData(): array
    {
        return [
            'posts' => Post::count(),
            'publishedPosts' => Post::where('status', 'published')->count(),
            'draftPosts' => Post::where('status', 'draft')->count(),
            'projects' => Project::count(),
            'featuredProjects' => Project::where('is_featured', true)->count(),
            'subscribers' => Subscriber::count(),
            'videos' => Video::count(),
            'pendingTestimonials' => Testimonial::where('status', 'pending')->count(),
        ];
    }
}

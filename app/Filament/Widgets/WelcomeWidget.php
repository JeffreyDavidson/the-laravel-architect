<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use App\Models\Project;
use App\Models\Subscriber;
use App\Models\Video;
use App\Models\Testimonial;
use Filament\Widgets\Widget;

class WelcomeWidget extends Widget
{
    protected static string $view = 'filament.widgets.welcome-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = -10;

    protected function getViewData(): array
    {
        return [
            'posts' => Post::count(),
            'projects' => Project::count(),
            'subscribers' => Subscriber::count(),
            'videos' => Video::count(),
            'pendingTestimonials' => Testimonial::where('status', 'pending')->count(),
        ];
    }
}

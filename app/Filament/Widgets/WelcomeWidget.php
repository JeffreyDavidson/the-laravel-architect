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

    protected function getViewData(): array
    {
        $postStats = Post::query()
            ->selectRaw(
                'count(*) as total, '.
                'sum(case when status = ? then 1 else 0 end) as published, '.
                'sum(case when status = ? then 1 else 0 end) as draft',
                [PublishStatus::Published->value, PublishStatus::Draft->value],
            )
            ->first();

        $projectStats = Project::query()
            ->selectRaw(
                'count(*) as total, '.
                'sum(case when is_featured = 1 then 1 else 0 end) as featured',
            )
            ->first();

        return [
            'posts' => (int) ($postStats->total ?? 0),
            'publishedPosts' => (int) ($postStats->published ?? 0),
            'draftPosts' => (int) ($postStats->draft ?? 0),
            'projects' => (int) ($projectStats->total ?? 0),
            'featuredProjects' => (int) ($projectStats->featured ?? 0),
            'subscribers' => Subscriber::count(),
            'videos' => Video::count(),
            'pendingTestimonials' => Testimonial::where('status', 'pending')->count(),
        ];
    }
}

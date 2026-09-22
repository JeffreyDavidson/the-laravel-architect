<?php

namespace App\Filament\Widgets;

use App\Enums\ContactInquiryStatus;
use App\Enums\PublishStatus;
use App\Models\ContactInquiry;
use App\Models\Post;
use Filament\Widgets\Widget;

class WelcomeWidget extends Widget
{
    #[\Override]
    protected string $view = 'filament.widgets.welcome-widget';

    #[\Override]
    protected int|string|array $columnSpan = 'full';

    #[\Override]
    protected static ?int $sort = -10;

    /**
     * @return array{
     *     posts: int,
     *     publishedPosts: int,
     *     draftPosts: int,
     *     inReviewPosts: int,
     *     scheduledPosts: int,
     *     newInquiries: int,
     * }
     */
    protected function getViewData(): array
    {
        return [
            'posts' => Post::query()->count(),
            'publishedPosts' => Post::query()->published()->count(),
            'draftPosts' => Post::query()->where('status', PublishStatus::Draft)->count(),
            'inReviewPosts' => Post::query()->where('status', PublishStatus::InReview)->count(),
            'scheduledPosts' => Post::query()->scheduled()->count(),
            'newInquiries' => ContactInquiry::query()->where('status', ContactInquiryStatus::New)->count(),
        ];
    }
}

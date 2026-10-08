<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Queries\AdminMetricsQuery;
use Filament\Widgets\Widget;

final class WelcomeWidget extends Widget
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
        $metrics = app(AdminMetricsQuery::class);

        return [
            'posts' => $metrics->totalPosts(),
            'publishedPosts' => $metrics->publishedPosts(),
            'draftPosts' => $metrics->draftPosts(),
            'inReviewPosts' => $metrics->postsInReview(),
            'scheduledPosts' => $metrics->scheduledPosts(),
            'newInquiries' => $metrics->newContactInquiries(),
        ];
    }
}

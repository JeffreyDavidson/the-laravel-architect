<?php

declare(strict_types=1);

namespace App\Publishing;

use App\Enums\ContentReadinessArea;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Counts the records in each dashboard readiness area that still fail one of the
 * area's checks, using one aggregate query per area.
 */
final readonly class ContentReadinessSummaryQuery
{
    public function __construct(private ContentReadinessCriteria $readiness) {}

    public function count(ContentReadinessArea $area): int
    {
        return $this->incomplete($area)
            ->count();
    }

    /**
     * The records in the area that fail at least one of its checks.
     *
     * @return Builder<covariant Model>
     */
    public function incomplete(ContentReadinessArea $area): Builder
    {
        $query = $this->records($area);

        $this->readiness->whereIncomplete($query, $area->checks());

        return $query;
    }

    /**
     * Every record the area reports on, complete or not.
     *
     * @return Builder<covariant Model>
     */
    public function records(ContentReadinessArea $area): Builder
    {
        return match ($area) {
            ContentReadinessArea::ProjectPreviews, ContentReadinessArea::ProjectStories => Project::query(),
            ContentReadinessArea::PodcastLinks => Podcast::query()->active(),
            ContentReadinessArea::EpisodeDetails => Episode::query(),
            ContentReadinessArea::PostContent => Post::query(),
            ContentReadinessArea::NewsletterIssues => NewsletterIssue::query(),
            ContentReadinessArea::VideoMetadata => Video::query(),
        };
    }
}

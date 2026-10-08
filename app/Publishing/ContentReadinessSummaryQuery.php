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
use Illuminate\Support\Facades\Cache;

/**
 * Counts the records in each dashboard readiness area that still fail one of the
 * area's checks, using one aggregate query per area.
 */
final readonly class ContentReadinessSummaryQuery
{
    public const string CACHE_KEY = 'content-readiness.outstanding-counts';

    private const int CACHE_SECONDS = 60;

    public function __construct(private ContentReadinessCriteria $readiness) {}

    /**
     * The incomplete record count of every area, keyed by area value. The dashboard reads it
     * on every load, so the counts are cached for a minute.
     *
     * @return array<string, int>
     */
    public function outstandingCounts(): array
    {
        /** @var array<string, int> $counts */
        $counts = Cache::remember(
            self::CACHE_KEY,
            now()->addSeconds(self::CACHE_SECONDS),
            fn (): array => collect(ContentReadinessArea::cases())
                ->mapWithKeys(fn (ContentReadinessArea $area): array => [$area->value => $this->count($area)])
                ->all(),
        );

        return $counts;
    }

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

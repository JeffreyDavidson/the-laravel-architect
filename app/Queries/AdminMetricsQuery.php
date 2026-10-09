<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\ContactInquiry;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Subscriber;
use App\Models\Video;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use JeffreyDavidson\CreatorKit\Enums\ContactInquiryStatus;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use JeffreyDavidson\CreatorKit\Support\Time\DisplayTimezone;

/**
 * The counts the admin panel shows on its dashboard widgets, navigation badges and
 * confirmation modals. Every place that shows a count reads it here, so a badge and a
 * dashboard stat always agree. Nothing is cached: each count is one aggregate query on
 * a small or indexed table, and a stale cache made badges lag behind the dashboard.
 */
final readonly class AdminMetricsQuery
{
    public function totalPosts(): int
    {
        return Post::query()->count();
    }

    public function draftPosts(): int
    {
        return Post::query()
            ->where('status', PublishStatus::Draft)
            ->count();
    }

    public function postsInReview(): int
    {
        return Post::query()
            ->where('status', PublishStatus::InReview)
            ->count();
    }

    /** Posts live on the site now. */
    public function publishedPosts(): int
    {
        return Post::query()
            ->published()
            ->count();
    }

    /** Posts set to go live at a future date. */
    public function scheduledPosts(): int
    {
        return Post::query()
            ->scheduled()
            ->count();
    }

    /** Live episodes of the shows marked active. */
    public function publishedEpisodesOfActivePodcasts(): int
    {
        return Episode::query()
            ->published()
            ->whereHas('podcast', fn (Builder $query): Builder => $query->where('is_active', true))
            ->count();
    }

    /** Episodes that are not live yet, including those scheduled for a future date. */
    public function unpublishedEpisodes(): int
    {
        return Episode::query()
            ->unpublished()
            ->count();
    }

    public function publishedNewsletterIssues(): int
    {
        return NewsletterIssue::query()
            ->published()
            ->count();
    }

    /** Newsletter issues that are not live yet, including those scheduled for a future date. */
    public function unpublishedNewsletterIssues(): int
    {
        return NewsletterIssue::query()
            ->unpublished()
            ->count();
    }

    public function activePodcasts(): int
    {
        return Podcast::query()
            ->active()
            ->count();
    }

    /** Subscribers a newsletter issue is sent to. */
    public function activeSubscribers(): int
    {
        return Subscriber::query()
            ->active()
            ->count();
    }

    public function newContactInquiries(): int
    {
        return ContactInquiry::query()
            ->where('status', ContactInquiryStatus::New)
            ->count();
    }

    /** Total YouTube views across the synced videos. */
    public function youTubeViews(): int
    {
        return (int) Video::query()->sum('view_count');
    }

    /**
     * Published posts, episodes and newsletter issues per display-timezone month. The months
     * are the starts of consecutive display-timezone months, oldest first, and each list holds
     * one count per month in the same order.
     *
     * @param  list<CarbonInterface>  $months
     * @return array{posts: list<int>, episodes: list<int>, newsletterIssues: list<int>}
     */
    public function publishedPerMonth(array $months): array
    {
        return [
            'posts' => $this->monthlyCounts(Post::query()->published(), $months),
            'episodes' => $this->monthlyCounts(Episode::query()->published(), $months),
            'newsletterIssues' => $this->monthlyCounts(NewsletterIssue::query()->published(), $months),
        ];
    }

    /**
     * Count the query's records per display-timezone month, starting from the first month.
     *
     * @param  Builder<Post>|Builder<Episode>|Builder<NewsletterIssue>  $query
     * @param  list<CarbonInterface>  $months
     * @return list<int>
     */
    private function monthlyCounts(Builder $query, array $months): array
    {
        $counts = array_fill_keys(array_map(fn (CarbonInterface $month): string => $month->format('Y-m'), $months), 0);
        $start = ($months[0] ?? null)
            ?->copy()
            ->utc();

        foreach ($query->where('published_at', '>=', $start)
            ->pluck('published_at') as $publishedAt) {
            if (! is_string($publishedAt) && ! $publishedAt instanceof DateTimeInterface) {
                continue;
            }

            $monthKey = DisplayTimezone::convert(Carbon::parse($publishedAt))
                ->format('Y-m');

            if (array_key_exists($monthKey, $counts)) {
                $counts[$monthKey]++;
            }
        }

        return array_values($counts);
    }
}

<?php

declare(strict_types=1);

namespace App\Queries;

use App\Data\CalendarEntry;
use App\Enums\CalendarEntryType;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Episode;
use App\Models\Post;
use App\Support\DisplayTimezone;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EditorialCalendarQuery
{
    /**
     * Posts and episodes published between the given display-timezone days, plus undated content,
     * ordered by publication date with undated entries last.
     *
     * @return Collection<int, CalendarEntry>
     */
    public function get(CarbonInterface $from, CarbonInterface $until): Collection
    {
        $range = [
            $from->copy()
                ->startOfDay()
                ->utc(),
            $until->copy()
                ->endOfDay()
                ->utc(),
        ];

        $posts = $this->datedWithinOrUndated(Post::query(), $range)
            ->get()
            ->map(fn (Post $post): CalendarEntry => $this->entry(
                record: $post,
                type: CalendarEntryType::Post,
                url: PostResource::getUrl('edit', ['record' => $post]),
            ));

        $episodes = $this->datedWithinOrUndated(Episode::query(), $range)
            ->get()
            ->map(fn (Episode $episode): CalendarEntry => $this->entry(
                record: $episode,
                type: CalendarEntryType::Episode,
                url: EpisodeResource::getUrl('edit', ['record' => $episode]),
            ));

        return $posts->concat($episodes)
            ->sortBy(fn (CalendarEntry $entry): string => $entry->date ?? '9999-12-31')
            ->values();
    }

    /**
     * @template TModel of Post|Episode
     *
     * @param  Builder<TModel>  $query
     * @param  array{CarbonInterface, CarbonInterface}  $range
     * @return Builder<TModel>
     */
    private function datedWithinOrUndated(Builder $query, array $range): Builder
    {
        return $query
            ->select(['id', 'title', 'status', 'published_at'])
            ->where(fn (Builder $query): Builder => $query
                ->whereBetween('published_at', $range)
                ->orWhereNull('published_at'))
            ->orderBy('published_at')
            ->orderBy('id');
    }

    private function entry(Post|Episode $record, CalendarEntryType $type, string $url): CalendarEntry
    {
        return new CalendarEntry(
            date: DisplayTimezone::convert($record->publishedAt())
                ?->toDateString(),
            title: $record->title,
            type: $type,
            status: $record->publishStatus(),
            url: $url,
        );
    }
}

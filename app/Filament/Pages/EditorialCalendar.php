<?php

namespace App\Filament\Pages;

use App\Enums\PublishStatus;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Episode;
use App\Models\Post;
use BackedEnum;
use Carbon\CarbonInterface;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use UnitEnum;

/**
 * @phpstan-type CalendarEntry array{date: string|null, title: string, type: string, typeKey: string, statusLabel: string, statusColor: string, url: string}
 * @phpstan-type UnscheduledEntry array{date: null, title: string, type: string, typeKey: string, statusLabel: string, statusColor: string, url: string}
 * @phpstan-type ScheduledCalendarEntry array{date: string, title: string, type: string, typeKey: string, statusLabel: string, statusColor: string, url: string}
 * @phpstan-type CalendarDay array{date: string, day: int, isCurrentMonth: bool, isToday: bool, entries: Collection<int, ScheduledCalendarEntry>}
 */
class EditorialCalendar extends Page
{
    #[\Override]
    protected static ?string $title = 'Editorial Calendar';

    #[\Override]
    protected static ?string $navigationLabel = 'Editorial Calendar';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    #[\Override]
    protected static string|UnitEnum|null $navigationGroup = 'Content';

    #[\Override]
    protected static ?int $navigationSort = 0;

    #[\Override]
    protected string $view = 'filament.pages.editorial-calendar';

    public string $month;

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function previousMonth(): void
    {
        $this->month = $this->selectedMonth()->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = $this->selectedMonth()->addMonth()->format('Y-m');
    }

    public function currentMonth(): void
    {
        $this->month = now()->format('Y-m');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('previousMonth')
                ->label('Previous month')
                ->icon(Heroicon::OutlinedChevronLeft)
                ->action(function (): void {
                    $this->previousMonth();
                }),
            Action::make('currentMonth')
                ->label('Today')
                ->icon(Heroicon::OutlinedCalendarDays)
                ->action(function (): void {
                    $this->currentMonth();
                }),
            Action::make('nextMonth')
                ->label('Next month')
                ->icon(Heroicon::OutlinedChevronRight)
                ->iconPosition('after')
                ->action(function (): void {
                    $this->nextMonth();
                }),
        ];
    }

    /**
     * @return array{calendarMonth: Carbon, weeks: Collection<int, Collection<int, CalendarDay>>, unscheduled: Collection<int, UnscheduledEntry>, statuses: array<string, int>}
     */
    protected function getViewData(): array
    {
        $month = $this->selectedMonth();
        $gridStart = $month->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $gridEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);
        $entries = $this->entries($gridStart, $gridEnd);
        /** @var Collection<int, CalendarDay> $days */
        $days = collect();

        for ($date = $gridStart->copy(); $date <= $gridEnd; $date->addDay()) {
            $dateKey = $date->toDateString();

            $days->push([
                'date' => $dateKey,
                'day' => $date->day,
                'isCurrentMonth' => $date->month === $month->month,
                'isToday' => $date->isToday(),
                'entries' => $this->entriesForDate($entries, $dateKey),
            ]);
        }

        return [
            'calendarMonth' => $month,
            'weeks' => $days->chunk(7)->values(),
            'unscheduled' => $entries->filter(fn (array $entry): bool => $entry['date'] === null)->values(),
            'statuses' => $entries
                ->countBy('statusLabel')
                ->mapWithKeys(fn (int $count, string|int $status): array => [(string) $status => $count])
                ->all(),
        ];
    }

    /**
     * @param  Collection<int, CalendarEntry>  $entries
     * @return Collection<int, ScheduledCalendarEntry>
     */
    private function entriesForDate(Collection $entries, string $dateKey): Collection
    {
        return $entries
            ->filter(fn (array $entry): bool => $entry['date'] === $dateKey)
            ->map(fn (array $entry): array => [
                'date' => (string) $entry['date'],
                'title' => $entry['title'],
                'type' => $entry['type'],
                'typeKey' => $entry['typeKey'],
                'statusLabel' => $entry['statusLabel'],
                'statusColor' => $entry['statusColor'],
                'url' => $entry['url'],
            ])
            ->values();
    }

    private function selectedMonth(): Carbon
    {
        try {
            $month = Carbon::createFromFormat('!Y-m', $this->month);
        } catch (\Throwable) {
            $month = null;
        }

        return $month instanceof Carbon ? $month : now()->startOfMonth();
    }

    /**
     * @return Collection<int, CalendarEntry>
     */
    private function entries(Carbon $gridStart, Carbon $gridEnd): Collection
    {
        $range = [$gridStart->copy()->startOfDay(), $gridEnd->copy()->endOfDay()];

        $posts = Post::query()
            ->select(['id', 'title', 'status', 'published_at'])
            ->where(fn (Builder $query): Builder => $query
                ->whereBetween('published_at', $range)
                ->orWhereNull('published_at'))
            ->orderBy('published_at')
            ->orderBy('id')
            ->get()
            ->map(fn (Post $post): array => $this->entry(
                title: $post->title,
                type: 'Post',
                typeKey: 'post',
                status: $post->publishStatus(),
                publishedAt: $post->publishedAt(),
                url: PostResource::getUrl('edit', ['record' => $post]),
            ));

        $episodes = Episode::query()
            ->select(['id', 'title', 'status', 'published_at'])
            ->where(fn (Builder $query): Builder => $query
                ->whereBetween('published_at', $range)
                ->orWhereNull('published_at'))
            ->orderBy('published_at')
            ->orderBy('id')
            ->get()
            ->map(fn (Episode $episode): array => $this->entry(
                title: $episode->title,
                type: 'Episode',
                typeKey: 'episode',
                status: $episode->publishStatus(),
                publishedAt: $episode->publishedAt(),
                url: EpisodeResource::getUrl('edit', ['record' => $episode]),
            ));

        return $posts->concat($episodes)
            ->sortBy(fn (array $entry): string => $entry['date'] ?? '9999-12-31')
            ->values();
    }

    /**
     * @return CalendarEntry
     */
    private function entry(
        string $title,
        string $type,
        string $typeKey,
        PublishStatus $status,
        ?CarbonInterface $publishedAt,
        string $url,
    ): array {
        return [
            'date' => $publishedAt?->toDateString(),
            'title' => $title,
            'type' => $type,
            'typeKey' => $typeKey,
            'statusLabel' => $status->label(),
            'statusColor' => $status->color(),
            'url' => $url,
        ];
    }
}

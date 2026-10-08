<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Data\CalendarEntry;
use App\Enums\CalendarEntryType;
use App\Enums\NavigationGroup;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Episode;
use App\Models\Post;
use App\Queries\EditorialCalendarQuery;
use App\Support\DisplayTimezone;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use UnitEnum;

/**
 * @phpstan-type CalendarDay array{date: string, day: int, isCurrentMonth: bool, isToday: bool, entries: Collection<int, CalendarEntry>}
 */
final class EditorialCalendar extends Page
{
    #[\Override]
    protected static ?string $title = 'Editorial Calendar';

    #[\Override]
    protected static ?string $navigationLabel = 'Editorial Calendar';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    #[\Override]
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Publish;

    #[\Override]
    protected static ?int $navigationSort = 0;

    #[\Override]
    protected string $view = 'filament.pages.editorial-calendar';

    public string $month;

    public function mount(): void
    {
        $this->month = now(DisplayTimezone::name())->format('Y-m');
    }

    public function previousMonth(): void
    {
        $this->month = $this->selectedMonth()
            ->subMonth()
            ->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = $this->selectedMonth()
            ->addMonth()
            ->format('Y-m');
    }

    public function currentMonth(): void
    {
        $this->month = now(DisplayTimezone::name())->format('Y-m');
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
     * @return array{calendarMonth: Carbon, weeks: Collection<int, Collection<int, CalendarDay>>, unscheduled: Collection<int, CalendarEntry>, statuses: array<string, int>}
     */
    protected function getViewData(): array
    {
        $month = $this->selectedMonth();
        $gridStart = $month->copy()
            ->startOfMonth()
            ->startOfWeek(Carbon::SUNDAY);
        $gridEnd = $month->copy()
            ->endOfMonth()
            ->endOfWeek(Carbon::SATURDAY);
        $entries = app(EditorialCalendarQuery::class)
            ->get($gridStart, $gridEnd)
            ->map(fn (Post|Episode $record): CalendarEntry => $this->entry($record))
            ->sortBy(fn (CalendarEntry $entry): string => $entry->date ?? '9999-12-31')
            ->values();
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
            'weeks' => $days->chunk(7)
                ->values(),
            'unscheduled' => $entries->filter(fn (CalendarEntry $entry): bool => $entry->date === null)
                ->values(),
            'statuses' => $entries
                ->countBy(fn (CalendarEntry $entry): string => $entry->status->getLabel())
                ->mapWithKeys(fn (int $count, string|int $status): array => [(string) $status => $count])
                ->all(),
        ];
    }

    /** The record as the calendar shows it: its publication day in the display timezone and its edit link. */
    private function entry(Post|Episode $record): CalendarEntry
    {
        return new CalendarEntry(
            date: DisplayTimezone::convert($record->publishedAt())
                ?->toDateString(),
            title: $record->title,
            type: $record instanceof Post ? CalendarEntryType::Post : CalendarEntryType::Episode,
            status: $record->publishStatus(),
            url: $record instanceof Post
                ? PostResource::getUrl('edit', ['record' => $record])
                : EpisodeResource::getUrl('edit', ['record' => $record]),
        );
    }

    /**
     * @param  Collection<int, CalendarEntry>  $entries
     * @return Collection<int, CalendarEntry>
     */
    private function entriesForDate(Collection $entries, string $dateKey): Collection
    {
        return $entries
            ->filter(fn (CalendarEntry $entry): bool => $entry->date === $dateKey)
            ->values();
    }

    private function selectedMonth(): Carbon
    {
        try {
            $month = Carbon::createFromFormat('!Y-m', $this->month, DisplayTimezone::name());
        } catch (\Throwable) {
            $month = null;
        }

        return $month instanceof Carbon ? $month : now(DisplayTimezone::name())->startOfMonth();
    }
}

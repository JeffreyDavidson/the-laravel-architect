<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Podcasts\PodcastResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Services\MediaHealthReport;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class MediaHealth extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $title = 'Media Health';

    protected static ?string $navigationLabel = 'Media Health';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.media-health';

    public function table(Table $table): Table
    {
        return $table
            ->records(function (MediaHealthReport $report, ?string $search, array $filters): array {
                $records = collect($report->records());

                if (filled($search)) {
                    $search = Str::lower($search);
                    $records = $records->filter(fn (array $record): bool => Str::contains(
                        Str::lower($record['title'].' '.$record['filename']),
                        $search,
                    ));
                }

                $type = $this->filterValue($filters, 'type');

                if ($type !== null) {
                    $records = $records->filter(fn (array $record): bool => $record['type_key'] === $type);
                }

                $status = $this->filterValue($filters, 'status');

                if ($status !== null) {
                    $records = $records->filter(fn (array $record): bool => $record['status'] === $status);
                }

                return $records->all();
            })
            ->columns([
                TextColumn::make('type')
                    ->label('Content')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('title')
                    ->label('Record')
                    ->searchable()
                    ->description(fn (array $record): string => $this->recordString($record, 'filename')),
                TextColumn::make('dimensions'),
                TextColumn::make('file_size')
                    ->label('Size'),
                TextColumn::make('source_status')
                    ->label('Source')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Optimized' => 'success',
                        'Needs optimization' => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('variants')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Ready' ? 'success' : 'warning'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (array $record): string => $this->recordString($record, 'status_color')),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'project' => 'Project',
                        'post' => 'Post',
                        'podcast' => 'Podcast',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'Healthy' => 'Healthy',
                        'Needs repair' => 'Needs repair',
                        'Re-upload required' => 'Re-upload required',
                    ]),
            ])
            ->recordActions([
                Action::make('repair')
                    ->label('Repair variants')
                    ->icon(Heroicon::OutlinedWrenchScrewdriver)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (array $record): bool => $this->recordBool($record, 'repairable'))
                    ->action(function (array $record, MediaHealthReport $report): void {
                        if (! $report->repair(
                            $this->recordString($record, 'type_key'),
                            $this->recordString($record, 'record_key'),
                        )) {
                            Notification::make()
                                ->title('Repair failed')
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Responsive variants repaired')
                            ->success()
                            ->send();

                        $this->resetTable();
                    }),
                Action::make('edit')
                    ->label('Edit')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->url(fn (array $record): string => $this->editUrl($record)),
            ])
            ->paginated(false)
            ->emptyStateHeading('No stored images')
            ->emptyStateDescription('Images added to projects, posts, and podcasts will appear here.');
    }

    private function recordString(mixed $record, string $key): string
    {
        if (! is_array($record)) {
            return '';
        }

        $value = $record[$key] ?? null;

        return is_string($value) ? $value : '';
    }

    private function filterValue(mixed $filters, string $key): ?string
    {
        if (! is_array($filters) || ! is_array($filter = $filters[$key] ?? null)) {
            return null;
        }

        $value = $filter['value'] ?? null;

        return is_string($value) && filled($value) ? $value : null;
    }

    private function recordBool(mixed $record, string $key): bool
    {
        if (! is_array($record)) {
            return false;
        }

        return ($record[$key] ?? false) === true;
    }

    private function editUrl(mixed $record): string
    {
        $type = $this->recordString($record, 'type_key');
        $recordKey = $this->recordString($record, 'record_key');

        return match ($type) {
            'project' => ProjectResource::getUrl('edit', ['record' => $recordKey]),
            'post' => PostResource::getUrl('edit', ['record' => $recordKey]),
            'podcast' => PodcastResource::getUrl('edit', ['record' => $recordKey]),
            default => ProjectResource::getUrl('index'),
        };
    }
}

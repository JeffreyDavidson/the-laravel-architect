<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\RepairImageVariants;
use App\Data\MediaHealthRecord;
use App\Enums\MediaHealthStatus;
use App\Enums\MediaHealthType;
use App\Enums\MediaSourceStatus;
use App\Enums\MediaVariantStatus;
use App\Enums\NavigationGroup;
use App\Filament\Resources\Podcasts\PodcastResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Queries\MediaHealthQuery;
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
use Illuminate\Support\Number;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\PostResource;
use UnitEnum;

final class MediaHealth extends Page implements HasTable
{
    use InteractsWithTable;

    #[\Override]
    protected static ?string $title = 'Media Health';

    #[\Override]
    protected static ?string $navigationLabel = 'Media Health';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    #[\Override]
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Operations;

    #[\Override]
    protected static ?int $navigationSort = 1;

    #[\Override]
    protected string $view = 'filament.pages.media-health';

    public function table(Table $table): Table
    {
        return $table
            ->records(function (MediaHealthQuery $query, ?string $search, array $filters): array {
                $type = $this->filterValue($filters, 'type');
                $status = $this->filterValue($filters, 'status');
                $typeFilter = $type === null ? null : MediaHealthType::tryFrom($type);
                $statusFilter = $status === null ? null : MediaHealthStatus::tryFrom($status);

                // A filter value that is not a known option matches nothing.
                if (($type !== null && ! $typeFilter instanceof MediaHealthType) || ($status !== null && ! $statusFilter instanceof MediaHealthStatus)) {
                    return [];
                }

                return collect($query->get($typeFilter, $search, $statusFilter))
                    ->mapWithKeys(fn (MediaHealthRecord $record): array => [$record->key() => $this->row($record)])
                    ->all();
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
                    ->formatStateUsing(fn (string $state): string => MediaSourceStatus::tryFrom($state)?->getLabel() ?? $state)
                    ->color(fn (string $state): string => MediaSourceStatus::tryFrom($state)?->getColor() ?? 'danger'),
                TextColumn::make('variants')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => MediaVariantStatus::tryFrom($state)?->getLabel() ?? $state)
                    ->color(fn (string $state): string => MediaVariantStatus::tryFrom($state)?->getColor() ?? 'warning'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => MediaHealthStatus::tryFrom($state)?->getLabel() ?? $state)
                    ->color(fn (array $record): string => $this->recordString($record, 'status_color')),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(MediaHealthType::class),
                SelectFilter::make('status')
                    ->options(MediaHealthStatus::class),
            ])
            ->recordActions([
                Action::make('repair')
                    ->label('Repair variants')
                    ->icon(Heroicon::OutlinedWrenchScrewdriver)
                    ->color('warning')
                    ->authorize('update')
                    ->requiresConfirmation()
                    ->visible(fn (array $record): bool => $this->recordBool($record, 'repairable'))
                    ->action(function (array $record, RepairImageVariants $repairImageVariants): void {
                        $type = MediaHealthType::tryFrom($this->recordString($record, 'type_key'));

                        if (! $type instanceof MediaHealthType || ! $repairImageVariants->handle($type, $this->recordString($record, 'record_key'))) {
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

    /**
     * Format one record as a table row, keeping the enum values for the badge columns.
     *
     * @return array{type: string, type_key: string, record_key: string, title: string, filename: string, dimensions: string, file_size: string, source_status: string, variants: string, status: string, status_color: string, repairable: bool}
     */
    private function row(MediaHealthRecord $record): array
    {
        return [
            'type' => $record->type->getLabel(),
            'type_key' => $record->type->value,
            'record_key' => $record->recordKey,
            'title' => $record->title,
            'filename' => $record->filename ?? '—',
            'dimensions' => $record->width === null || $record->height === null
                ? '—'
                : "{$record->width} × {$record->height}",
            'file_size' => $record->fileSize === null
                ? '—'
                : Number::fileSize($record->fileSize),
            'source_status' => $record->sourceStatus->value,
            'variants' => $record->variantStatus->value,
            'status' => $record->status->value,
            'status_color' => $record->status->getColor(),
            'repairable' => $record->repairable,
        ];
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

        return match (MediaHealthType::tryFrom($type)) {
            MediaHealthType::Project => ProjectResource::getUrl('edit', ['record' => $recordKey]),
            MediaHealthType::Post => PostResource::getUrl('edit', ['record' => $recordKey]),
            MediaHealthType::Podcast => PodcastResource::getUrl('edit', ['record' => $recordKey]),
            default => ProjectResource::getUrl('index'),
        };
    }
}

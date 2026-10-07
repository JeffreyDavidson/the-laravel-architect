<?php

namespace App\Filament\Resources\Episodes\Tables;

use App\Enums\PublishStatus;
use App\Filament\Actions\ViewOnSiteAction;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Tables\Columns\ReadinessColumn;
use App\Models\Episode;
use App\Presenters\EpisodePresenter;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EpisodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->with(['podcast', 'seo'])
                ->withCount('tags'))
            ->columns([
                TextColumn::make('episode_number')
                    ->label('#')
                    ->state(fn (Episode $record): string => EpisodePresenter::from($record)->code())
                    ->sortable(['season_number', 'episode_number']),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                ReadinessColumn::make(),
                TextColumn::make('podcast.name')
                    ->label('Podcast')
                    ->sortable(),
                TextColumn::make('guest_name')
                    ->label('Guest')
                    ->placeholder('Solo')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('duration_seconds')
                    ->label('Duration')
                    ->state(fn (Episode $record): string => EpisodePresenter::from($record)->duration())
                    ->toggleable(),
                TextColumn::make('transistor')
                    ->label('Transistor')
                    ->state(fn (Episode $record): string => $record->transistorEmbedUrl() === null ? 'Not added' : 'Available')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Available' ? 'success' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('M j, Y')
                    ->placeholder('Not published')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('unpublished')
                    ->label('Not yet published')
                    ->query(self::filterUnpublished(...)),
                SelectFilter::make('status')
                    ->options(PublishStatus::labels(includeInReview: false)),
                Filter::make('missing_transistor_url')
                    ->label('Missing Transistor URL')
                    ->query(fn (Builder $query): Builder => $query->whereIn('episodes.id', Episode::query()
                        ->published()
                        ->where(fn (Builder $query): Builder => $query->whereNull('transistor_url')
                            ->orWhere('transistor_url', ''))
                        ->select('id'))),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->url(fn (Episode $record): string => EpisodeResource::getUrl('edit', ['record' => $record])),
                ViewOnSiteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('episode_number', 'desc')
            ->emptyStateIcon(Heroicon::OutlinedMusicalNote)
            ->emptyStateHeading('No episodes yet')
            ->emptyStateDescription('Create an episode when its Transistor link, show notes, or a recording plan is ready.');
    }

    /**
     * @param  Builder<Episode>  $query
     * @return Builder<Episode>
     */
    private static function filterUnpublished(Builder $query): Builder
    {
        return $query->unpublished();
    }
}

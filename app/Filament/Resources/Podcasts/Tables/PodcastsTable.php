<?php

namespace App\Filament\Resources\Podcasts\Tables;

use App\Enums\ContentReadinessStatus;
use App\Models\Podcast;
use App\Support\Content\ContentReadiness;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PodcastsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('seo'))
            ->columns([
                ImageColumn::make('cover_image_path')
                    ->disk('public')
                    ->circular()
                    ->label('Cover'),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('readiness')
                    ->label('Readiness')
                    ->state(fn (Podcast $record): ContentReadinessStatus => new ContentReadiness($record)->status())
                    ->description(fn (Podcast $record): string => new ContentReadiness($record)->missingSummary())
                    ->badge(),
                TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('episodes_count')
                    ->counts('episodes')
                    ->label('Episodes')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                TextColumn::make('sort_order')
                    ->sortable()
                    ->label('Order'),
            ])
            ->defaultSort('sort_order')
            ->filters([
                //
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedMicrophone)
            ->emptyStateHeading('No podcasts yet')
            ->emptyStateDescription('Create a show before adding its first episode.');
    }
}

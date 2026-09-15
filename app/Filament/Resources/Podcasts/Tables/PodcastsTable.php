<?php

namespace App\Filament\Resources\Podcasts\Tables;

use App\Models\Podcast;
use App\Support\Content\ContentReadiness;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
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
                    ->state(fn (Podcast $record): string => new ContentReadiness($record)->label())
                    ->description(fn (Podcast $record): string => (new ContentReadiness($record))->missingSummary())
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Ready' ? 'success' : 'warning'),
                TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(),
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
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

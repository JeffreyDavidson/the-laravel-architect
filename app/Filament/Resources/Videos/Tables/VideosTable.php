<?php

declare(strict_types=1);

namespace App\Filament\Resources\Videos\Tables;

use App\Filament\Resources\Videos\VideoResource;
use App\Models\Video;
use App\Support\Content\ContentReadiness;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class VideosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail_url')
                    ->label('Thumb')
                    ->width(120)
                    ->height(68),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('readiness')
                    ->label('Readiness')
                    ->state(fn (Video $record): string => new ContentReadiness($record)->label())
                    ->description(fn (Video $record): string => new ContentReadiness($record)->missingSummary())
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Ready' ? 'success' : 'warning'),
                TextColumn::make('view_count')
                    ->label('Views')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('like_count')
                    ->label('Likes')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('comment_count')
                    ->label('Comments')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),
                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('M j, Y')
                    ->sortable(),
                TextColumn::make('synced_at')
                    ->label('Last Sync')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_featured')
                    ->label('Featured'),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (Video $record): string => VideoResource::getUrl('edit', ['record' => $record])),
            ])
            ->defaultSort('published_at', 'desc')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedVideoCamera)
            ->emptyStateHeading('No videos yet')
            ->emptyStateDescription('Synced YouTube videos will appear here for editorial review.');
    }
}

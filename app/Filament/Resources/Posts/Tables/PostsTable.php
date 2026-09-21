<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\PublishStatus;
use App\Models\Post;
use App\Support\Content\ContentReadiness;
use App\Support\Content\PreviewUrlGenerator;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->with(['category', 'seo'])
                ->withCount('tags'))
            ->columns([
                ImageColumn::make('featured_image_path')
                    ->label('Image')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn (): string => 'https://ui-avatars.com/api/?name=P&background=6366f1&color=fff'),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('readiness')
                    ->label('Readiness')
                    ->state(fn (Post $record): string => new ContentReadiness($record)->label())
                    ->description(fn (Post $record): string => new ContentReadiness($record)->missingSummary())
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Ready' ? 'success' : 'warning'),
                TextColumn::make('author.name')
                    ->label('Author')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('category.name')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (PublishStatus $state): string => $state->color()),
                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('M j, Y')
                    ->placeholder('Not published')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(PublishStatus::labels()),
                SelectFilter::make('category')
                    ->relationship('category', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('view_on_site')
                    ->label('View on site')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Post $record, PreviewUrlGenerator $previewUrlGenerator): string => $record->isPublished()
                        ? route('blog.show', $record)
                        : $previewUrlGenerator->for($record))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon(Heroicon::OutlinedDocumentText)
            ->emptyStateHeading('No posts yet')
            ->emptyStateDescription('Start a draft when the next Laravel idea is ready to develop.');
    }
}

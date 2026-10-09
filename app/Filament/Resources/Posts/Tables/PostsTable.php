<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\SourceReviewStatus;
use App\Filament\Tables\Columns\ReadinessColumn;
use App\Filament\Tables\Filters\PublicationFilter;
use App\Models\Post;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Vite;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use JeffreyDavidson\CreatorKit\Filament\Actions\ViewOnSiteAction;

final class PostsTable
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
                    ->defaultImageUrl(fn (): string => Vite::asset('resources/images/admin-post-placeholder.svg')),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                ReadinessColumn::make(),
                TextColumn::make('author.name')
                    ->label('Author')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('category.name')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('M j, Y')
                    ->placeholder('Not published')
                    ->sortable(),
                TextColumn::make('source_review_status')
                    ->label('Source review')
                    ->state(fn (Post $record): SourceReviewStatus => $record->sourceReviewStatus())
                    ->badge()
                    ->toggleable(),
                TextColumn::make('last_reviewed_at')
                    ->label('Reviewed')
                    ->date()
                    ->sortable()
                    ->placeholder('Not tracked')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(PublishStatus::class),
                PublicationFilter::make(),
                SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Filter::make('review_due')
                    ->label('Source review due')
                    ->query(fn (Builder $query): Builder => $query->whereIn('posts.id', Post::query()
                        ->reviewDue()
                        ->select('id'))),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                ViewOnSiteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon(Heroicon::OutlinedDocumentText)
            ->emptyStateHeading('No posts yet')
            ->emptyStateDescription('Start a draft when the next Laravel idea is ready to develop.');
    }
}

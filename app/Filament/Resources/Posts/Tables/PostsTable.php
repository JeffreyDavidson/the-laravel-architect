<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\PublishStatus;
use App\Enums\SourceReviewStatus;
use App\Filament\Actions\ViewOnSiteAction;
use App\Filament\Tables\Columns\ReadinessColumn;
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
                    ->options(PublishStatus::labels()),
                SelectFilter::make('publication')
                    ->label('Publication')
                    ->options([
                        PublishStatus::Published->value => 'Live on the site',
                        PublishStatus::Scheduled->value => 'Scheduled for later',
                    ])
                    ->query(self::filterPublication(...)),
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

    /**
     * @param  Builder<Post>  $query
     * @param  array<string, mixed>  $data
     * @return Builder<Post>
     */
    private static function filterPublication(Builder $query, array $data): Builder
    {
        return match ($data['value'] ?? null) {
            PublishStatus::Published->value => $query->published(),
            PublishStatus::Scheduled->value => $query->scheduled(),
            default => $query,
        };
    }
}

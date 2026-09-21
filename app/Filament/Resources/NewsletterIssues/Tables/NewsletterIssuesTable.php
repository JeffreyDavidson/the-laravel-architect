<?php

namespace App\Filament\Resources\NewsletterIssues\Tables;

use App\Enums\PublishStatus;
use App\Models\NewsletterIssue;
use App\Support\Content\ContentReadiness;
use App\Support\Content\PreviewUrlGenerator;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NewsletterIssuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('seo'))
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(60),
                TextColumn::make('readiness')
                    ->label('Readiness')
                    ->state(fn (NewsletterIssue $record): string => new ContentReadiness($record)->label())
                    ->description(fn (NewsletterIssue $record): string => new ContentReadiness($record)->missingSummary())
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Ready' ? 'success' : 'warning'),
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
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('view_on_site')
                    ->label('View on site')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (NewsletterIssue $record, PreviewUrlGenerator $previewUrlGenerator): string => $record->isPublished()
                        ? route('newsletter.issue', $record)
                        : $previewUrlGenerator->for($record))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->emptyStateIcon(Heroicon::OutlinedNewspaper)
            ->emptyStateHeading('No newsletter issues yet')
            ->emptyStateDescription('Draft the first issue when there is an update worth sending.');
    }
}

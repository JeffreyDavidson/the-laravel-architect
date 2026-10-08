<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsletterIssues\Tables;

use App\Enums\PublishStatus;
use App\Filament\Actions\ViewOnSiteAction;
use App\Filament\Tables\Columns\ReadinessColumn;
use App\Filament\Tables\Filters\PublicationFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class NewsletterIssuesTable
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
                ReadinessColumn::make(),
                TextColumn::make('status')
                    ->badge(),
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
                    ->options(PublishStatus::class),
                PublicationFilter::make(),
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
            ->defaultSort('updated_at', 'desc')
            ->emptyStateIcon(Heroicon::OutlinedNewspaper)
            ->emptyStateHeading('No newsletter issues yet')
            ->emptyStateDescription('Draft the first issue when there is an update worth sending.');
    }
}

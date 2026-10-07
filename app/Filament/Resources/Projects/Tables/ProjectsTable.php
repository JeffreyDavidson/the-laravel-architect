<?php

declare(strict_types=1);

namespace App\Filament\Resources\Projects\Tables;

use App\Enums\ProjectReadinessFilter;
use App\Filament\Actions\ViewOnSiteAction;
use App\Filament\Tables\Columns\ReadinessColumn;
use App\Queries\ProjectReadinessQuery;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->with('seo')
                ->withCount('tags'))
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                ReadinessColumn::make()
                    ->withProgress(),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('featured_image_path')
                    ->label('Image')
                    ->disk('public'),
                TextColumn::make('url')
                    ->label('Live URL')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('github_url')
                    ->label('GitHub URL')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('readiness')
                    ->options(ProjectReadinessFilter::class)
                    ->query(function (Builder $query, array $data, ProjectReadinessQuery $readinessQuery): void {
                        $readinessQuery->apply($query, is_string($data['value'] ?? null) ? $data['value'] : null);
                    }),
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
            ->defaultSort('sort_order')
            ->emptyStateIcon(Heroicon::OutlinedCodeBracket)
            ->emptyStateHeading('No projects yet')
            ->emptyStateDescription('Add a project when it is ready to become part of the public portfolio.');
    }
}

<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Enums\PublishStatus;
use App\Models\Project;
use App\Queries\ProjectReadinessQuery;
use App\Support\Content\ContentReadiness;
use App\Support\Content\PreviewUrlGenerator;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProjectsTable
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
                TextColumn::make('readiness')
                    ->label('Readiness')
                    ->state(fn (Project $record): string => new ContentReadiness($record)->label())
                    ->description(function (Project $record): string {
                        $readiness = new ContentReadiness($record);

                        return $readiness->progress().' · '.$readiness->missingSummary();
                    })
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Ready' ? 'success' : 'warning'),
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
                    ->badge()
                    ->color(fn (PublishStatus $state): string => $state->color()),
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
                    ->options([
                        'ready' => 'Ready',
                        'needs_image' => 'Needs image',
                        'needs_case_study' => 'Needs case study',
                        'needs_details' => 'Needs project details',
                    ])
                    ->query(function (Builder $query, array $data, ProjectReadinessQuery $readinessQuery): void {
                        $readinessQuery->apply($query, is_string($data['value'] ?? null) ? $data['value'] : null);
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('preview')
                    ->label('Preview')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn (Project $record, PreviewUrlGenerator $previewUrlGenerator): string => $previewUrlGenerator->for($record))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->emptyStateIcon(Heroicon::OutlinedCodeBracket)
            ->emptyStateHeading('No projects yet')
            ->emptyStateDescription('Add a project when it is ready to become part of the public portfolio.');
    }
}

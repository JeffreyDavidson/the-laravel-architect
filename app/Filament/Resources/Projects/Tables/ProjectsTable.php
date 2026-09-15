<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Models\Project;
use App\Support\Content\PreviewUrlGenerator;
use App\Support\Content\ProjectReadiness;
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
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('tags'))
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('readiness')
                    ->label('Readiness')
                    ->state(fn (Project $record): string => new ProjectReadiness($record)->label())
                    ->description(function (Project $record): string {
                        $readiness = new ProjectReadiness($record);

                        return $readiness->progress().' · '.$readiness->missingSummary();
                    })
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Ready' ? 'success' : 'warning'),
                TextColumn::make('slug')
                    ->searchable(),
                ImageColumn::make('featured_image_path')
                    ->label('Image')
                    ->disk('public'),
                TextColumn::make('url')
                    ->label('Live URL')
                    ->searchable(),
                TextColumn::make('github_url')
                    ->label('GitHub URL')
                    ->searchable(),
                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
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
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        'ready' => $query
                            ->whereNotNull('description')
                            ->where('description', '!=', '')
                            ->whereNotNull('content')
                            ->where('content', '!=', '')
                            ->whereNotNull('featured_image_path')
                            ->where('featured_image_path', '!=', '')
                            ->where(fn (Builder $query): Builder => $query
                                ->where('url', '!=', '')
                                ->whereNotNull('url')
                                ->orWhere(fn (Builder $query): Builder => $query->whereNotNull('github_url')->where('github_url', '!=', '')))
                            ->whereNotNull('tech_stack')
                            ->where('tech_stack', '!=', '[]')
                            ->whereHas('tags'),
                        'needs_image' => $query->where(fn (Builder $query): Builder => $query
                            ->whereNull('featured_image_path')
                            ->orWhere('featured_image_path', '')),
                        'needs_case_study' => $query->where(fn (Builder $query): Builder => $query
                            ->whereNull('content')
                            ->orWhere('content', '')),
                        'needs_details' => $query->where(function (Builder $query): void {
                            $query
                                ->whereNull('description')
                                ->orWhere('description', '')
                                ->orWhere(function (Builder $query): void {
                                    $query
                                        ->where(fn (Builder $query): Builder => $query->whereNull('url')->orWhere('url', ''))
                                        ->where(fn (Builder $query): Builder => $query->whereNull('github_url')->orWhere('github_url', ''));
                                })
                                ->orWhereNull('tech_stack')
                                ->orWhere('tech_stack', '[]')
                                ->orWhereDoesntHave('tags');
                        }),
                        default => $query,
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
            ]);
    }
}

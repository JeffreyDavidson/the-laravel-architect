<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Models\Project;
use App\Support\Content\PreviewUrlGenerator;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
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
                //
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

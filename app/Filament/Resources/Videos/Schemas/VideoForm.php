<?php

namespace App\Filament\Resources\Videos\Schemas;

use App\Models\Video;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VideoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Video Details')
                ->schema([
                    TextInput::make('title')
                        ->disabled(),
                    TextInput::make('youtube_id')
                        ->label('YouTube ID')
                        ->disabled(),
                    TextInput::make('youtube_url')
                        ->label('YouTube URL')
                        ->formatStateUsing(fn (Video $record): string => "https://youtube.com/watch?v={$record->youtube_id}")
                        ->disabled(),
                    Toggle::make('is_featured')
                        ->label('Featured on homepage'),
                ]),
            Section::make('Stats')
                ->schema([
                    TextInput::make('view_count')
                        ->label('Views')
                        ->formatStateUsing(fn (Video $record): string => number_format($record->view_count))
                        ->disabled(),
                    TextInput::make('like_count')
                        ->label('Likes')
                        ->formatStateUsing(fn (Video $record): string => number_format($record->like_count))
                        ->disabled(),
                    TextInput::make('comment_count')
                        ->label('Comments')
                        ->formatStateUsing(fn (Video $record): string => number_format($record->comment_count))
                        ->disabled(),
                    TextInput::make('synced_at')
                        ->label('Last Synced')
                        ->formatStateUsing(fn (Video $record): string => $record->synced_at?->diffForHumans() ?? 'Never')
                        ->disabled(),
                ]),
        ]);
    }
}

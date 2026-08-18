<?php

namespace App\Filament\Resources\Videos\Schemas;

use App\Models\Video;
use Filament\Forms\Components\Placeholder;
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
                    Placeholder::make('youtube_url')
                        ->label('YouTube URL')
                        ->content(fn (Video $record): string => "https://youtube.com/watch?v={$record->youtube_id}"),
                    Toggle::make('is_featured')
                        ->label('Featured on homepage'),
                ]),
            Section::make('Stats')
                ->schema([
                    Placeholder::make('view_count')
                        ->label('Views')
                        ->content(fn (Video $record): string => number_format($record->view_count)),
                    Placeholder::make('like_count')
                        ->label('Likes')
                        ->content(fn (Video $record): string => number_format($record->like_count)),
                    Placeholder::make('comment_count')
                        ->label('Comments')
                        ->content(fn (Video $record): string => number_format($record->comment_count)),
                    Placeholder::make('synced_at')
                        ->label('Last Synced')
                        ->content(fn (Video $record): string => $record->synced_at?->diffForHumans() ?? 'Never'),
                ]),
        ]);
    }
}

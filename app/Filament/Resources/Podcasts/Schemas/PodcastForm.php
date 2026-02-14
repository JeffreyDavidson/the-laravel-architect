<?php

namespace App\Filament\Resources\Podcasts\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PodcastForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Show Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Textarea::make('description')
                            ->required()
                            ->rows(3)
                            ->helperText('Short tagline')
                            ->columnSpanFull(),
                        Textarea::make('long_description')
                            ->rows(5)
                            ->helperText('Full about section for the podcast page')
                            ->columnSpanFull(),
                        FileUpload::make('cover_image')
                            ->image()
                            ->directory('podcasts'),
                        ColorPicker::make('color')
                            ->default('#6366f1')
                            ->helperText('Brand color for this show'),
                    ])->columns(2),

                Section::make('Subscribe Links')
                    ->schema([
                        TextInput::make('apple_url')->label('Apple Podcasts URL')->url(),
                        TextInput::make('spotify_url')->label('Spotify URL')->url(),
                        TextInput::make('rss_url')->label('RSS Feed URL')->url(),
                        TextInput::make('youtube_url')->label('YouTube URL')->url(),
                    ])->columns(2),

                Section::make('Settings')
                    ->schema([
                        Toggle::make('is_active')->label('Active')->default(true),
                        TextInput::make('sort_order')->numeric()->default(0),
                    ])->columns(2),
            ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\Podcasts\Schemas;

use App\Filament\Forms\Components\OptimizedImageUpload;
use App\Filament\Forms\Components\SlugInput;
use App\Filament\Forms\Components\SlugSourceInput;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use RalphJSmit\Filament\SEO\SEO;

final class PodcastForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Show Details')
                    ->schema([
                        SlugSourceInput::make('name')
                            ->required()
                            ->maxLength(255),
                        SlugInput::make('slug'),
                        Textarea::make('description')
                            ->required()
                            ->rows(3)
                            ->helperText('Short tagline')
                            ->columnSpanFull(),
                        Textarea::make('long_description')
                            ->rows(5)
                            ->helperText('Full about section for the podcast page')
                            ->columnSpanFull(),
                        OptimizedImageUpload::make('cover_image_path')
                            ->disk('public')
                            ->directory('podcasts'),
                        ColorPicker::make('color')
                            ->default('#6366f1')
                            ->helperText('Brand color for this show'),
                    ])->columns(2),

                Section::make('Subscribe Links')
                    ->schema([
                        TextInput::make('apple_url')->label('Apple Podcasts URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('spotify_url')->label('Spotify URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('rss_url')->label('RSS Feed URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('youtube_url')->label('YouTube URL')
                            ->url()
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Settings')
                    ->schema([
                        Toggle::make('is_active')->label('Active')
                            ->default(true),
                        TextInput::make('sort_order')->numeric()
                            ->default(0),
                    ])->columns(2),

                Section::make('SEO')
                    ->schema([
                        SEO::make(),
                    ])
                    ->collapsed(),
            ]);
    }
}

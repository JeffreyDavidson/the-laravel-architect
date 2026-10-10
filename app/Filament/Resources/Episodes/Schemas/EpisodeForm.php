<?php

declare(strict_types=1);

namespace App\Filament\Resources\Episodes\Schemas;

use App\Filament\Forms\Components\OptimizedImageUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\PublishDatePicker;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\PublishStatusSelect;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\SeoSection;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\SlugInput;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\SlugSourceInput;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\TransistorUrlInput;

final class EpisodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Episode Details')
                    ->schema([
                        Select::make('podcast_id')
                            ->relationship('podcast', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                        SlugSourceInput::make('title')
                            ->required()
                            ->maxLength(255),
                        SlugInput::make('slug')
                            ->lockedAfterPublication(),
                        TextInput::make('episode_number')
                            ->numeric()
                            ->label('Episode #'),
                        TextInput::make('season_number')
                            ->numeric()
                            ->default(1)
                            ->label('Season #'),
                        Textarea::make('description')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        MarkdownEditor::make('show_notes')
                            ->label('Show Notes')
                            ->columnSpanFull(),
                        MarkdownEditor::make('transcript')
                            ->label('Transcript')
                            ->helperText('Optional episode transcript. Markdown is supported.')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Media')
                    ->schema([
                        TransistorUrlInput::make('transistor_url'),
                        TextInput::make('youtube_url')
                            ->label('YouTube URL')
                            ->url()
                            ->maxLength(255)
                            ->helperText('If episode is also on YouTube'),
                        OptimizedImageUpload::make('featured_image_path')
                            ->disk('public')
                            ->directory('episodes/images'),
                        TextInput::make('duration_seconds')
                            ->integer()
                            ->minValue(0)
                            ->maxValue(2147483647)
                            ->suffix('seconds')
                            ->label('Duration')
                            ->helperText('Total length in seconds (for example 1500 for 25 minutes).'),
                    ])->columns(2),

                Section::make('Guest')
                    ->schema([
                        TextInput::make('guest_name')
                            ->maxLength(255),
                        TextInput::make('guest_title')
                            ->maxLength(255)
                            ->helperText('e.g. Senior Dev at Acme Corp'),
                        TextInput::make('guest_url')
                            ->url()
                            ->maxLength(255)
                            ->helperText('Guest website or social link'),
                    ])->columns(3)
                    ->collapsed(),

                Section::make('Publishing')
                    ->schema([
                        SpatieTagsInput::make('tags'),
                        PublishStatusSelect::make('status')
                            ->withoutReview(),
                        PublishDatePicker::make('published_at'),
                    ])->columns(3),

                SeoSection::make(),
            ]);
    }
}

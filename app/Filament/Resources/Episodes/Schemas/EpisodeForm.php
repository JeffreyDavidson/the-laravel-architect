<?php

namespace App\Filament\Resources\Episodes\Schemas;

use App\Enums\PublishStatus;
use App\Filament\Forms\Components\OptimizedImageUpload;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use RalphJSmit\Filament\SEO\SEO;

class EpisodeForm
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
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation): void {
                                if ($operation === 'create' && blank($get('slug'))) {
                                    $set('slug', Str::slug($state ?? ''));
                                }
                            }),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->regex('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/')
                            ->unique(),
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
                        TextInput::make('audio_url')
                            ->label('Audio URL')
                            ->url()
                            ->maxLength(255)
                            ->helperText('Link to hosted audio (Buzzsprout, Anchor, etc.)'),
                        FileUpload::make('audio_path')
                            ->disk('public')
                            ->directory('episodes/audio')
                            ->acceptedFileTypes(['audio/mpeg', 'audio/wav', 'audio/x-wav'])
                            ->maxSize(256000),
                        TextInput::make('embed_url')
                            ->label('Embed URL')
                            ->url()
                            ->maxLength(255)
                            ->helperText('Spotify/Apple embed URL'),
                        TextInput::make('youtube_url')
                            ->label('YouTube URL')
                            ->url()
                            ->maxLength(255)
                            ->helperText('If episode is also on YouTube'),
                        OptimizedImageUpload::make('featured_image_path')
                            ->disk('public')
                            ->directory('episodes/images')
                            ->maxSize(10240),
                        TextInput::make('duration_minutes')
                            ->numeric()
                            ->label('Duration (minutes)'),
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
                        Select::make('status')
                            ->options(PublishStatus::labels(includeInReview: false))
                            ->default(PublishStatus::Draft)
                            ->required(),
                        DateTimePicker::make('published_at')
                            ->label('Publish Date'),
                    ])->columns(3),

                Section::make('SEO')
                    ->schema([
                        SEO::make(),
                    ])
                    ->collapsed(),
            ]);
    }
}

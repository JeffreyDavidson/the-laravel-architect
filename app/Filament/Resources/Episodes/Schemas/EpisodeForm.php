<?php

namespace App\Filament\Resources\Episodes\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

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
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
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
                    ])->columns(2),

                Section::make('Media')
                    ->schema([
                        TextInput::make('audio_url')
                            ->label('Audio URL')
                            ->url()
                            ->helperText('Link to hosted audio (Buzzsprout, Anchor, etc.)'),
                        FileUpload::make('audio_file')
                            ->label('Or Upload Audio')
                            ->directory('episodes')
                            ->acceptedFileTypes(['audio/mpeg', 'audio/mp3', 'audio/wav']),
                        TextInput::make('embed_url')
                            ->label('Embed URL')
                            ->url()
                            ->helperText('Spotify/Apple embed URL'),
                        TextInput::make('youtube_url')
                            ->label('YouTube URL')
                            ->url()
                            ->helperText('If episode is also on YouTube'),
                        FileUpload::make('featured_image')
                            ->image()
                            ->directory('episodes'),
                        TextInput::make('duration_minutes')
                            ->numeric()
                            ->label('Duration (minutes)'),
                    ])->columns(2),

                Section::make('Guest')
                    ->schema([
                        TextInput::make('guest_name'),
                        TextInput::make('guest_title')
                            ->helperText('e.g. Senior Dev at Acme Corp'),
                        TextInput::make('guest_url')
                            ->url()
                            ->helperText('Guest website or social link'),
                    ])->columns(3)
                    ->collapsed(),

                Section::make('Publishing')
                    ->schema([
                        Select::make('tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'scheduled' => 'Scheduled',
                            ])
                            ->default('draft')
                            ->required(),
                        DateTimePicker::make('published_at')
                            ->label('Publish Date'),
                    ])->columns(3),
            ]);
    }
}

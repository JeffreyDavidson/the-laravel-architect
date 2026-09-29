<?php

namespace App\Filament\Resources\Episodes\Schemas;

use App\Filament\Forms\Components\OptimizedImageUpload;
use App\Filament\Forms\Components\PublishStatusSelect;
use App\Models\Episode;
use Filament\Forms\Components\DateTimePicker;
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
                            ->disabled(fn (?Episode $record): bool => $record?->isSlugLocked() ?? false)
                            ->helperText('URLs stay locked after first publication, even when unpublished.')
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
                        TextInput::make('transistor_url')
                            ->label('Transistor episode URL')
                            ->url()
                            ->maxLength(255)
                            ->rules(['regex:/\Ahttps:\/\/share\.transistor\.fm\/s\/[a-zA-Z0-9]+\/?\z/'])
                            ->validationMessages(['regex' => 'Paste the episode share URL, such as https://share.transistor.fm/s/428dcd6b.'])
                            ->helperText('The public page shows the Transistor player for this episode.'),
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

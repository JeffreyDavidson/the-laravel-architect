<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Forms\Components\OptimizedImageUpload;
use App\Filament\Forms\Components\PublishDatePicker;
use App\Filament\Forms\Components\PublishStatusSelect;
use App\Models\Category;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
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

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content')
                    ->schema([
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
                            ->disabled(fn (?Post $record): bool => $record?->isSlugLocked() ?? false)
                            ->helperText('URLs stay locked after first publication, even when unpublished.')
                            ->required()
                            ->maxLength(255)
                            ->regex('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/')
                            ->unique(),
                        Textarea::make('excerpt')
                            ->rows(3)
                            ->helperText('Brief summary shown in post listings')
                            ->columnSpanFull(),
                        MarkdownEditor::make('content')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Media & Metadata')
                    ->schema([
                        OptimizedImageUpload::make('featured_image_path')
                            ->disk('public')
                            ->directory('posts')
                            ->columnSpanFull(),
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionAction(fn (Action $action): Action => $action->authorize('create', Category::class))
                            ->createOptionForm([
                                TextInput::make('name')->required()
                                    ->maxLength(255),
                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->regex('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/')
                                    ->unique(Category::class),
                            ]),
                        Select::make('episodes')
                            ->label('Related Episodes')
                            ->relationship('episodes', 'title')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                        SpatieTagsInput::make('tags'),
                    ])->columns(2),

                Section::make('Publishing')
                    ->schema([
                        PublishStatusSelect::make('status'),
                        PublishDatePicker::make('published_at'),
                        Hidden::make('user_id')
                            ->default(fn () => auth()->id()),
                    ])->columns(2),

                Section::make('Review')
                    ->schema([
                        Textarea::make('review_notes')
                            ->label('Review Notes')
                            ->rows(3)
                            ->helperText('Feedback from the reviewer')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(fn (?Post $record): bool => $record?->review_notes === null),

                Section::make('Review & Source')
                    ->description('Optional. Track the official source this post relies on so it is flagged for review as it ages.')
                    ->schema([
                        TextInput::make('source_url')
                            ->label('Official source')
                            ->url()
                            ->maxLength(255)
                            ->required(fn (Get $get): bool => filled($get('last_reviewed_at')))
                            ->helperText('Link to the documentation or primary source this post relies on.'),
                        DatePicker::make('last_reviewed_at')
                            ->label('Last reviewed')
                            ->required(fn (Get $get): bool => filled($get('source_url')))
                            ->helperText(fn (): string => 'Sourced posts are flagged after '.config()->integer('content.post_review_interval_days').' days.'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(fn (?Post $record): bool => blank($record?->getAttribute('source_url'))),

                Section::make('SEO')
                    ->schema([
                        SEO::make(),
                    ])
                    ->collapsed(),
            ]);
    }
}

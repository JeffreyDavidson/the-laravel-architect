<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
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
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->regex('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/')
                            ->unique(ignoreRecord: true),
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
                        FileUpload::make('featured_image_path')
                            ->disk('public')
                            ->directory('posts')
                            ->image()
                            ->maxSize(10240)
                            ->columnSpanFull(),
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionAction(fn (Action $action): Action => $action->authorize('create', Category::class))
                            ->createOptionForm([
                                TextInput::make('name')->required(),
                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->regex('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/')
                                    ->unique(Category::class),
                            ]),
                        SpatieTagsInput::make('tags'),
                    ])->columns(2),

                Section::make('Publishing')
                    ->schema([
                        Select::make('status')
                            ->options(PublishStatus::labels())
                            ->default(PublishStatus::Draft)
                            ->required(),
                        DateTimePicker::make('published_at')
                            ->label('Publish Date'),
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

                Section::make('SEO')
                    ->schema([
                        SEO::make(),
                    ])
                    ->collapsed(),
            ]);
    }
}

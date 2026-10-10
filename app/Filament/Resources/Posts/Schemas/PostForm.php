<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Forms\Components\OptimizedImageUpload;
use App\Models\Category;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\PublishDatePicker;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\PublishStatusSelect;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\ReviewNotesSection;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\SeoSection;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\SlugInput;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\SlugSourceInput;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\SourceReviewSection;

final class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content')
                    ->schema([
                        SlugSourceInput::make('title')
                            ->required()
                            ->maxLength(255),
                        SlugInput::make('slug')
                            ->lockedAfterPublication(),
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
                                SlugInput::make('slug'),
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

                ReviewNotesSection::make(),

                SourceReviewSection::make()
                    ->reviewIntervalDays(fn (): int => config()->integer('content.post_review_interval_days')),

                SeoSection::make(),
            ]);
    }
}

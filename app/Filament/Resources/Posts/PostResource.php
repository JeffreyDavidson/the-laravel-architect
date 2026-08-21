<?php

namespace App\Filament\Resources\Posts;

use App\Enums\PublishStatus;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Posts\Schemas\PostForm;
use App\Filament\Resources\Posts\Tables\PostsTable;
use App\Models\Post;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;
use UnitEnum;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationBadge(): ?string
    {
        $counts = Cache::remember('filament.navigation.posts-status-counts', now()->addMinutes(5), fn (): array => [
            'review' => static::getModel()::where('status', PublishStatus::InReview)->count(),
            'draft' => static::getModel()::where('status', PublishStatus::Draft)->count(),
        ]);
        $reviewCount = $counts['review'];
        $draftCount = $counts['draft'];

        if ($reviewCount > 0) {
            return $reviewCount.' to review';
        }

        return $draftCount > 0 ? $draftCount.' draft'.($draftCount > 1 ? 's' : '') : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $reviewCount = Cache::remember('filament.navigation.posts-review-count', now()->addMinutes(5), fn (): int => static::getModel()::where('status', PublishStatus::InReview)->count());

        return $reviewCount > 0 ? 'info' : 'gray';
    }

    public static function form(Schema $schema): Schema
    {
        return PostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }
}

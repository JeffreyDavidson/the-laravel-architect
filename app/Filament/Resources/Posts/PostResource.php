<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Posts\Schemas\PostForm;
use App\Filament\Resources\Posts\Tables\PostsTable;
use App\Models\Post;
use App\Queries\AdminMetricsQuery;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use JeffreyDavidson\CreatorKit\Filament\Concerns\ResolvesTrashedRecords;
use UnitEnum;

final class PostResource extends Resource
{
    use ResolvesTrashedRecords;

    #[\Override]
    protected static ?string $model = Post::class;

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    #[\Override]
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Publish;

    #[\Override]
    protected static ?int $navigationSort = 1;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationBadge(): ?string
    {
        $metrics = app(AdminMetricsQuery::class);
        $reviewCount = $metrics->postsInReview();

        if ($reviewCount > 0) {
            return "{$reviewCount} to review";
        }

        $draftCount = $metrics->draftPosts();

        return $draftCount > 0 ? "{$draftCount} ".Str::plural('draft', $draftCount) : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return app(AdminMetricsQuery::class)->postsInReview() > 0 ? 'info' : 'gray';
    }

    public static function form(Schema $schema): Schema
    {
        return PostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostsTable::configure($table);
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

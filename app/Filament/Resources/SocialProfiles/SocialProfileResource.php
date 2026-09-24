<?php

namespace App\Filament\Resources\SocialProfiles;

use App\Enums\SocialPlatform;
use App\Filament\Resources\SocialProfiles\Pages\CreateSocialProfile;
use App\Filament\Resources\SocialProfiles\Pages\EditSocialProfile;
use App\Filament\Resources\SocialProfiles\Pages\ListSocialProfiles;
use App\Filament\Resources\SocialProfiles\Schemas\SocialProfileForm;
use App\Filament\Resources\SocialProfiles\Tables\SocialProfilesTable;
use App\Models\SocialProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class SocialProfileResource extends Resource
{
    #[\Override]
    protected static ?string $model = SocialProfile::class;

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    #[\Override]
    protected static string|UnitEnum|null $navigationGroup = 'Audience';

    #[\Override]
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return SocialProfileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SocialProfilesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        if (! $record instanceof SocialProfile) {
            return static::getModelLabel();
        }

        $platformValue = $record->getAttribute('platform');
        $platform = match (true) {
            $platformValue instanceof SocialPlatform => $platformValue,
            is_string($platformValue) => SocialPlatform::tryFrom($platformValue),
            default => null,
        };
        $platformLabel = $platform?->getLabel() ?? static::getModelLabel();

        return $platformLabel.(filled($record->label) ? ' — '.$record->label : '');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSocialProfiles::route('/'),
            'create' => CreateSocialProfile::route('/create'),
            'edit' => EditSocialProfile::route('/{record}/edit'),
        ];
    }
}

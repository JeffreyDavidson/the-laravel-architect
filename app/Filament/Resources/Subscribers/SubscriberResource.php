<?php

declare(strict_types=1);

namespace App\Filament\Resources\Subscribers;

use App\Filament\Resources\Subscribers\Pages\ListSubscribers;
use App\Filament\Resources\Subscribers\Tables\SubscribersTable;
use App\Models\Subscriber;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SubscriberResource extends Resource
{
    #[\Override]
    protected static ?string $model = Subscriber::class;

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    #[\Override]
    protected static string|UnitEnum|null $navigationGroup = 'Audience';

    #[\Override]
    protected static ?string $recordTitleAttribute = 'email';

    #[\Override]
    protected static ?int $navigationSort = 7;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return SubscribersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscribers::route('/'),
        ];
    }
}

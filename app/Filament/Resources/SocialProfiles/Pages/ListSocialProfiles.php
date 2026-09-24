<?php

declare(strict_types=1);

namespace App\Filament\Resources\SocialProfiles\Pages;

use App\Filament\Resources\SocialProfiles\SocialProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSocialProfiles extends ListRecords
{
    #[\Override]
    protected static string $resource = SocialProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

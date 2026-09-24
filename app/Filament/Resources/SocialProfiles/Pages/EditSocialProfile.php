<?php

declare(strict_types=1);

namespace App\Filament\Resources\SocialProfiles\Pages;

use App\Filament\Resources\SocialProfiles\SocialProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSocialProfile extends EditRecord
{
    #[\Override]
    protected static string $resource = SocialProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

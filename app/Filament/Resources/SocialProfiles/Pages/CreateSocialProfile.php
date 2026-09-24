<?php

declare(strict_types=1);

namespace App\Filament\Resources\SocialProfiles\Pages;

use App\Filament\Resources\SocialProfiles\SocialProfileResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSocialProfile extends CreateRecord
{
    #[\Override]
    protected static string $resource = SocialProfileResource::class;
}

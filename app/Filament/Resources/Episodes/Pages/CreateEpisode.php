<?php

declare(strict_types=1);

namespace App\Filament\Resources\Episodes\Pages;

use App\Filament\Resources\Episodes\EpisodeResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateEpisode extends CreateRecord
{
    #[\Override]
    protected static string $resource = EpisodeResource::class;
}

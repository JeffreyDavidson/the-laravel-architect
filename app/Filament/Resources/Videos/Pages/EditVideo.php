<?php

declare(strict_types=1);

namespace App\Filament\Resources\Videos\Pages;

use App\Filament\Resources\Videos\VideoResource;
use Filament\Resources\Pages\EditRecord;

final class EditVideo extends EditRecord
{
    #[\Override]
    protected static string $resource = VideoResource::class;
}

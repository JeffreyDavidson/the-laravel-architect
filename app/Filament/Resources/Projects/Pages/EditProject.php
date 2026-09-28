<?php

declare(strict_types=1);

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Actions\PublishContentAction;
use App\Filament\Actions\UnpublishContentAction;
use App\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    #[\Override]
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PublishContentAction::make(),
            UnpublishContentAction::make(),
            DeleteAction::make(),
        ];
    }
}

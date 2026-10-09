<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use JeffreyDavidson\CreatorKit\Filament\Actions\PublishContentAction;
use JeffreyDavidson\CreatorKit\Filament\Actions\UnpublishContentAction;

final class EditPost extends EditRecord
{
    #[\Override]
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PublishContentAction::make(),
            UnpublishContentAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}

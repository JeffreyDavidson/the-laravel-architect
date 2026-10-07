<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Contracts\Publishable;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Takes live or scheduled content back to draft while keeping its publish date and permalink.
 */
final class UnpublishContentAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'unpublish';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->icon(Heroicon::OutlinedArrowUturnLeft)
            ->authorize('update')
            ->color('warning')
            ->requiresConfirmation()
            ->visible(fn (Publishable $record): bool => $record->isPublished() || $record->isScheduled())
            ->action(function (Model&Publishable $record, Component $livewire): void {
                $record->unpublish();

                if ($livewire instanceof EditRecord) {
                    $livewire->refreshFormData(array_keys($record->getChanges()));
                }

                Notification::make()
                    ->success()
                    ->title(Str::headline(class_basename($record)).' unpublished')
                    ->send();
            });
    }
}

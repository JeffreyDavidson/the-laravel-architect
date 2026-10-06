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
 * Publishes content once its required details are present, keeping any scheduled date.
 * On an edit page the form is saved first, so the publish date and details on screen are
 * the ones checked and published; invalid form data stops the action before publishing.
 */
class PublishContentAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'publish';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->icon(Heroicon::OutlinedRocketLaunch)
            ->authorize('update')
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (Publishable $record): bool => ! $record->isPublished() && ! $record->isScheduled())
            ->action(function (Model&Publishable $record, Component $livewire): void {
                if ($livewire instanceof EditRecord) {
                    $livewire->save(shouldRedirect: false, shouldSendSavedNotification: false);
                }

                $contentType = Str::headline(class_basename($record));
                $issues = $record->publishingIssues();

                if ($issues !== []) {
                    Notification::make()
                        ->danger()
                        ->title("{$contentType} is not ready to publish")
                        ->body(implode(' · ', $issues))
                        ->persistent()
                        ->send();

                    return;
                }

                $record->publish();

                if ($livewire instanceof EditRecord) {
                    $livewire->refreshFormData(array_keys($record->getChanges()));
                }

                Notification::make()
                    ->success()
                    ->title("{$contentType} published")
                    ->send();
            });
    }
}

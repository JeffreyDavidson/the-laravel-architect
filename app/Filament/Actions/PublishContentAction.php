<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Actions\PublishContent;
use App\Contracts\Publishable;
use App\Exceptions\ContentNotReadyToPublish;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\Project;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Publishes content through PublishContent, which refuses it while required details are
 * missing and keeps any scheduled date. On an edit page the form is saved first, so the
 * publish date and details on screen are the ones checked and published; invalid form
 * data stops the action before publishing.
 */
final class PublishContentAction extends Action
{
    public static function getDefaultName(): string
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
            ->action(function (Post|Project|Episode|NewsletterIssue $record, Component $livewire, PublishContent $publishContent): void {
                if ($livewire instanceof EditRecord) {
                    $livewire->save(shouldRedirect: false, shouldSendSavedNotification: false);
                }

                $contentType = Str::headline(class_basename($record));

                try {
                    $publishContent->handle($record);
                } catch (ContentNotReadyToPublish $exception) {
                    Notification::make()
                        ->danger()
                        ->title("{$contentType} is not ready to publish")
                        ->body($exception->issueSummary(' · '))
                        ->persistent()
                        ->send();

                    return;
                }

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

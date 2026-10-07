<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactInquiries\Pages;

use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Jobs\SendContactInquiryEmails;
use App\Models\ContactInquiry;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

final class EditContactInquiry extends EditRecord
{
    #[\Override]
    protected static string $resource = ContactInquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('retryEmails')
                ->label('Retry unsent emails')
                ->icon(Heroicon::OutlinedArrowPath)
                ->authorize('update')
                ->visible(fn (ContactInquiry $record): bool => $record->email_attempted_at !== null
                    && ($record->notification_sent_at === null || $record->confirmation_sent_at === null))
                ->disabled(fn (ContactInquiry $record): bool => ! $record->canRetryEmails())
                ->tooltip('Retries are available for 23 hours after submission. Older inquiries need a manual check with the mail provider.')
                ->requiresConfirmation()
                ->modalDescription('Only emails without a recorded successful send are retried. A provider timeout can leave delivery uncertain, so check the provider first.')
                ->action(function (ContactInquiry $record): void {
                    dispatch(new SendContactInquiryEmails($record->id));

                    Notification::make()
                        ->title('Email delivery queued')
                        ->body('Refresh this page shortly to check the delivery status.')
                        ->success()
                        ->send();
                }),
            DeleteAction::make()->authorize('delete'),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactInquiries\Pages;

use App\Actions\RetryContactInquiryEmails;
use App\Exceptions\ContactInquiryEmailsCannotBeRetried;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
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
                ->visible(fn (ContactInquiry $record, RetryContactInquiryEmails $retryEmails): bool => $retryEmails->hasUnsentEmails($record))
                ->disabled(fn (ContactInquiry $record): bool => ! $record->canRetryEmails())
                ->tooltip('Retries are available for 23 hours after submission. Older inquiries need a manual check with the mail provider.')
                ->requiresConfirmation()
                ->modalDescription('Only emails without a recorded successful send are retried. A provider timeout can leave delivery uncertain, so check the provider first.')
                ->action(function (ContactInquiry $record, RetryContactInquiryEmails $retryEmails): void {
                    try {
                        $retryEmails->handle($record);
                    } catch (ContactInquiryEmailsCannotBeRetried $exception) {
                        Notification::make()
                            ->danger()
                            ->title('Emails not retried')
                            ->body($exception->getMessage())
                            ->persistent()
                            ->send();

                        return;
                    }

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

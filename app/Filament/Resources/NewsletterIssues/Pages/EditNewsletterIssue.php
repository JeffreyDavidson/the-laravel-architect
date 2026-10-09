<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsletterIssues\Pages;

use App\Actions\SendNewsletterIssue;
use App\Actions\SendNewsletterIssueTestEmail;
use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Models\NewsletterIssue;
use App\Queries\AdminMetricsQuery;
use Carbon\CarbonInterface;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use JeffreyDavidson\CreatorKit\Filament\Actions\PublishContentAction;
use JeffreyDavidson\CreatorKit\Filament\Actions\UnpublishContentAction;
use JeffreyDavidson\CreatorKit\Support\Time\DisplayTimezone;
use UnexpectedValueException;

final class EditNewsletterIssue extends EditRecord
{
    #[\Override]
    protected static string $resource = NewsletterIssueResource::class;

    #[\Override]
    public function getSubheading(): ?string
    {
        $issue = $this->issue();
        $sentAt = $issue->getAttribute('sent_at');

        if (! $sentAt instanceof CarbonInterface) {
            return null;
        }

        $deliveries = app(AdminMetricsQuery::class)->newsletterDeliveries($issue);

        $sentOn = DisplayTimezone::convert($sentAt)
            ->format('M j, Y');

        return "Sent {$sentOn}. Delivered to {$deliveries->delivered} of {$deliveries->total} ".Str::plural('subscriber', $deliveries->total).'.';
    }

    protected function getHeaderActions(): array
    {
        return [
            PublishContentAction::make(),
            UnpublishContentAction::make(),
            Action::make('sendTestEmail')
                ->label('Send test email')
                ->icon(Heroicon::OutlinedEnvelope)
                ->color('gray')
                ->authorize('update')
                ->action(function (SendNewsletterIssueTestEmail $sendNewsletterIssueTestEmail): void {
                    $this->saveBeforeSending();
                    $issue = $this->issue();
                    $recipient = $sendNewsletterIssueTestEmail->handle($issue);

                    Notification::make()
                        ->title("Test email sent to {$recipient}")
                        ->success()
                        ->send();
                }),
            Action::make('sendToSubscribers')
                ->label('Send to subscribers')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->authorize('update')
                ->visible(
                    fn (): bool => $this->canSendToSubscribers(),
                )
                ->requiresConfirmation()
                ->modalHeading('Send this issue to subscribers?')
                ->modalDescription(function (AdminMetricsQuery $metrics): string {
                    $subscribers = $metrics->activeSubscribers();

                    return "This emails the issue to {$subscribers} active ".Str::plural('subscriber', $subscribers).'. It cannot be undone.';
                })
                ->modalSubmitActionLabel('Send')
                ->action(function (SendNewsletterIssue $sendNewsletterIssue): void {
                    $this->saveBeforeSending();
                    $issue = $this->issue();
                    $queued = $sendNewsletterIssue->handle($issue);

                    if ($queued === 0) {
                        Notification::make()
                            ->danger()
                            ->title('No active subscribers to send to')
                            ->body('Nothing was sent, and the issue can still be sent later.')
                            ->persistent()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title("Queued for {$queued} ".Str::plural('subscriber', $queued))
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /**
     * Save the form so the email matches what is on screen. Invalid data throws a
     * validation exception, which stops the action before anything is sent.
     */
    private function saveBeforeSending(): void
    {
        $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
    }

    private function canSendToSubscribers(): bool
    {
        $issue = $this->issue();

        return $issue->isPublished()
            && ! $issue->wasSent();
    }

    private function issue(): NewsletterIssue
    {
        $issue = $this->getRecord();

        if (! $issue instanceof NewsletterIssue) {
            throw new UnexpectedValueException('The edit page record must be a newsletter issue.');
        }

        return $issue;
    }
}

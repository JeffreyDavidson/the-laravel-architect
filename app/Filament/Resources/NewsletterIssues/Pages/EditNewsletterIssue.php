<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsletterIssues\Pages;

use App\Actions\SendNewsletterIssue;
use App\Actions\SendNewsletterIssueTestEmail;
use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Models\NewsletterIssue;
use App\Models\Subscriber;
use Carbon\CarbonInterface;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use UnexpectedValueException;

class EditNewsletterIssue extends EditRecord
{
    #[\Override]
    protected static string $resource = NewsletterIssueResource::class;

    #[\Override]
    public function getSubheading(): string|Htmlable|null
    {
        $issue = $this->issue();
        $sentAt = $issue->getAttribute('sent_at');

        if (! $sentAt instanceof CarbonInterface) {
            return null;
        }

        $total = $issue
            ->deliveries()
            ->count();
        $delivered = $issue
            ->deliveries()
            ->whereNotNull('sent_at')
            ->count();

        return "Sent {$sentAt->format('M j, Y')}. Delivered to {$delivered} of {$total} ".Str::plural('subscriber', $total).'.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendTestEmail')
                ->label('Send test email')
                ->icon(Heroicon::OutlinedEnvelope)
                ->color('gray')
                ->action(function (SendNewsletterIssueTestEmail $sendNewsletterIssueTestEmail): void {
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
                ->visible(
                    fn (): bool => $this->canSendToSubscribers(),
                )
                ->requiresConfirmation()
                ->modalHeading('Send this issue to subscribers?')
                ->modalDescription(function (): string {
                    $subscribers = Subscriber::query()
                        ->active()
                        ->count();

                    return "This emails the issue to {$subscribers} active ".Str::plural('subscriber', $subscribers).'. It cannot be undone.';
                })
                ->modalSubmitActionLabel('Send')
                ->action(function (SendNewsletterIssue $sendNewsletterIssue): void {
                    $issue = $this->issue();
                    $queued = $sendNewsletterIssue->handle($issue);

                    Notification::make()
                        ->title("Queued for {$queued} ".Str::plural('subscriber', $queued))
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
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

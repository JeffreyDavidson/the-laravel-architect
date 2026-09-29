<?php

namespace App\Models;

use App\Enums\ContactInquiryStatus;
use Database\Factories\ContactInquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

#[Fillable('name', 'email', 'type', 'budget', 'message', 'project_title', 'status', 'notes', 'email_attempted_at', 'notification_sent_at', 'confirmation_sent_at')]
class ContactInquiry extends Model
{
    /** @use HasFactory<ContactInquiryFactory> */
    use HasFactory, Prunable;

    /**
     * Contact details are encrypted at rest and this model intentionally does
     * not use activity logging, since audit records would duplicate the
     * private message content.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'encrypted',
            'email' => 'encrypted',
            'budget' => 'encrypted',
            'message' => 'encrypted',
            'project_title' => 'encrypted',
            'notes' => 'encrypted',
            'status' => ContactInquiryStatus::class,
            'email_attempted_at' => 'datetime',
            'notification_sent_at' => 'datetime',
            'confirmation_sent_at' => 'datetime',
        ];
    }

    /**
     * Whether the provider still honours the idempotency keys for this inquiry's emails.
     * Resend keeps keys for 24 hours, so resending later could deliver duplicates.
     */
    public function canRetryEmails(): bool
    {
        return $this->created_at !== null
            && $this->created_at->gte(now()->subHours(23));
    }

    /** @return Builder<ContactInquiry> */
    public function prunable(): Builder
    {
        return ContactInquiry::query()->where(
            'created_at',
            '<=',
            now()->subDays(config()->integer('contact.retention_days')),
        );
    }
}

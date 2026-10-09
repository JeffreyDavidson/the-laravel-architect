<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContactType;
use Database\Factories\ContactInquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JeffreyDavidson\CreatorKit\Contracts\ContactInquiry as ContactInquiryContract;
use JeffreyDavidson\CreatorKit\Models\Concerns\IsContactInquiry;

/**
 * @property ContactType $type
 */
#[Fillable('name', 'email', 'type', 'budget', 'message', 'project_title', 'status', 'notes', 'email_attempted_at', 'notification_sent_at', 'confirmation_sent_at')]
final class ContactInquiry extends Model implements ContactInquiryContract
{
    /** @use HasFactory<ContactInquiryFactory> */
    use HasFactory, IsContactInquiry;

    /**
     * creator-kit's IsContactInquiry adds the encrypted name, email and message,
     * the status and email-stamp casts, the retry window and pruning. These casts
     * are TLA's own. Contact details are encrypted at rest and this model
     * intentionally does not use activity logging, since audit records would
     * duplicate the private message content.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ContactType::class,
            'budget' => 'encrypted',
            'project_title' => 'encrypted',
            'notes' => 'encrypted',
        ];
    }
}

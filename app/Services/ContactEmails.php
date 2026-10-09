<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\ContactConfirmationMail;
use App\Mail\ContactInquiryReceivedMail;
use App\Models\ContactInquiry;
use Illuminate\Database\Eloquent\Model;
use JeffreyDavidson\CreatorKit\Contracts\ContactMails;
use LogicException;

/**
 * TLA's contact emails for creator-kit's contact job. Bound to the package's
 * `ContactMails` in `AppServiceProvider`; it lives outside `App\Mail` because every
 * class there is a mailable.
 */
final class ContactEmails implements ContactMails
{
    public function notification(Model $inquiry): ContactInquiryReceivedMail
    {
        return new ContactInquiryReceivedMail($this->inquiry($inquiry));
    }

    public function confirmation(Model $inquiry): ContactConfirmationMail
    {
        return new ContactConfirmationMail($this->inquiry($inquiry));
    }

    private function inquiry(Model $inquiry): ContactInquiry
    {
        if (! $inquiry instanceof ContactInquiry) {
            throw new LogicException('TLA\'s contact emails need a ContactInquiry.');
        }

        return $inquiry;
    }
}

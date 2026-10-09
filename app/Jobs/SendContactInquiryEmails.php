<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use JeffreyDavidson\CreatorKit\Jobs\SendContactInquiryEmails as PackageSendContactInquiryEmails;

/**
 * Forwards contact-email jobs queued before the move to creator-kit's job, which now sends
 * them. Jobs already in the queue or in `failed_jobs` name this class, so it stays until
 * none are left; see docs/operations/deploying.md for when to remove it.
 *
 * @deprecated Dispatch JeffreyDavidson\CreatorKit\Jobs\SendContactInquiryEmails instead.
 */
final class SendContactInquiryEmails implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public function __construct(public int $contactInquiryId) {}

    public function handle(): void
    {
        dispatch(new PackageSendContactInquiryEmails($this->contactInquiryId));
    }
}

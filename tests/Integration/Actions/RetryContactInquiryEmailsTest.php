<?php

use App\Actions\RetryContactInquiryEmails;
use App\Exceptions\ContactInquiryEmailsCannotBeRetried;
use App\Jobs\SendContactInquiryEmails;
use App\Models\ContactInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\assertDatabaseCount;

pest()->use(RefreshDatabase::class);

it('queues the email job again after an incomplete delivery attempt', function () {
    $inquiry = ContactInquiry::factory()->create([
        'email_attempted_at' => now(),
        'notification_sent_at' => now(),
    ]);

    app(RetryContactInquiryEmails::class)->handle($inquiry);

    assertDatabaseCount('jobs', 1);
    expect(DB::table('jobs')->value('payload'))
        ->toContain(addslashes(SendContactInquiryEmails::class));
});

it('refuses a retry when no email is waiting to be retried', function (?bool $emailsSent) {
    $inquiry = ContactInquiry::factory()->create([
        'email_attempted_at' => $emailsSent === null ? null : now(),
        'notification_sent_at' => $emailsSent === true ? now() : null,
        'confirmation_sent_at' => $emailsSent === true ? now() : null,
    ]);

    expect(fn () => app(RetryContactInquiryEmails::class)->handle($inquiry))
        ->toThrow(ContactInquiryEmailsCannotBeRetried::class, 'Every email for this inquiry was sent, or delivery has not been attempted yet.');

    assertDatabaseCount('jobs', 0);
})->with([
    'not attempted yet' => [null],
    'fully delivered' => [true],
]);

it('refuses a retry once the provider idempotency window has passed', function () {
    $inquiry = ContactInquiry::factory()->create([
        'created_at' => now()->subDay(),
        'email_attempted_at' => now()->subDay(),
    ]);

    expect(fn () => app(RetryContactInquiryEmails::class)->handle($inquiry))
        ->toThrow(ContactInquiryEmailsCannotBeRetried::class, 'Retries are available for 23 hours after submission.');

    assertDatabaseCount('jobs', 0);
});

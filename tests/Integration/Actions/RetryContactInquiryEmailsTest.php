<?php

use App\Models\ContactInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use JeffreyDavidson\CreatorKit\Actions\RetryContactInquiryEmails;
use JeffreyDavidson\CreatorKit\Exceptions\ContactInquiryEmailsCannotBeRetried;
use JeffreyDavidson\CreatorKit\Jobs\SendContactInquiryEmails;

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

it('refuses a retry when no email is waiting to be retried', function (array $deliveryState) {
    // Arrange
    /** @var array<string, mixed> $deliveryState */
    $inquiry = ContactInquiry::factory()->create($deliveryState);

    // Act
    $retry = fn () => app(RetryContactInquiryEmails::class)->handle($inquiry);

    // Assert
    expect($retry)
        ->toThrow(ContactInquiryEmailsCannotBeRetried::class, 'Every email for this inquiry was sent, or delivery has not been attempted yet.');
    assertDatabaseCount('jobs', 0);
})->with([
    'not attempted yet' => fn (): array => [
        'email_attempted_at' => null,
        'notification_sent_at' => null,
        'confirmation_sent_at' => null,
    ],
    'fully delivered' => fn (): array => [
        'email_attempted_at' => now(),
        'notification_sent_at' => now(),
        'confirmation_sent_at' => now(),
    ],
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

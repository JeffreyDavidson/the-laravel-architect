<?php

use App\Filament\Resources\ContactInquiries\Pages\EditContactInquiry;
use App\Models\ContactInquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use JeffreyDavidson\CreatorKit\Jobs\SendContactInquiryEmails;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()->create(['is_admin' => true])));

it('shows when each contact email was sent', function () {
    $inquiry = ContactInquiry::factory()->create([
        'email_attempted_at' => now(),
        'notification_sent_at' => now(),
        'confirmation_sent_at' => null,
    ]);

    livewire(EditContactInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->assertFormFieldExists('notification_sent_at')
        ->assertFormFieldDisabled('notification_sent_at')
        ->assertFormFieldExists('confirmation_sent_at')
        ->assertFormFieldDisabled('confirmation_sent_at');
});

it('offers a retry only after an incomplete delivery attempt', function (?bool $notificationSent, bool $visible) {
    $inquiry = ContactInquiry::factory()->create([
        'email_attempted_at' => $notificationSent === null ? null : now(),
        'notification_sent_at' => $notificationSent === true ? now() : null,
        'confirmation_sent_at' => $notificationSent === true ? now() : null,
    ]);

    $page = livewire(EditContactInquiry::class, ['record' => $inquiry->getRouteKey()]);

    $visible
        ? $page->assertActionVisible('retryEmails')
        : $page->assertActionHidden('retryEmails');
})->with([
    'not attempted yet' => [null, false],
    'attempted and incomplete' => [false, true],
    'fully delivered' => [true, false],
]);

it('queues the unsent emails again from the retry action', function () {
    $inquiry = ContactInquiry::factory()->create([
        'email_attempted_at' => now(),
        'notification_sent_at' => now(),
    ]);

    livewire(EditContactInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->callAction('retryEmails')
        ->assertNotified('Email delivery queued');

    assertDatabaseCount('jobs', 1);
    expect(DB::table('jobs')->value('payload'))
        ->toContain(addslashes(SendContactInquiryEmails::class));
});

it('disables the retry once the provider idempotency window has passed', function () {
    $inquiry = ContactInquiry::factory()->create([
        'created_at' => now()->subDay(),
        'email_attempted_at' => now()->subDay(),
    ]);

    livewire(EditContactInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->assertActionDisabled('retryEmails');
});

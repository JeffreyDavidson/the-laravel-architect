<?php

use App\Jobs\SendContactInquiryEmails;
use App\Mail\ContactConfirmationMail;
use App\Mail\ContactInquiryReceivedMail;
use App\Models\ContactInquiry;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\AssertableJsonString;

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('mail.contact_to', 'owner@example.com');
    Event::fake([MessageSent::class]);
});

function sendContactInquiryEmails(ContactInquiry $inquiry): void
{
    new SendContactInquiryEmails($inquiry->id)
        ->handle(app(Mailer::class));
}

it('records each successful send and does not resend on a later run', function () {
    $inquiry = ContactInquiry::factory()->create();

    sendContactInquiryEmails($inquiry);
    sendContactInquiryEmails($inquiry);

    $inquiry->refresh();
    expect($inquiry->email_attempted_at)
        ->not->toBeNull()
        ->and($inquiry->notification_sent_at)
        ->not->toBeNull()
        ->and($inquiry->confirmation_sent_at)
        ->not->toBeNull();
    Event::assertDispatchedTimes(MessageSent::class, 2);
});

it('sends the owner notification to the contact address and the confirmation to the sender', function () {
    $inquiry = ContactInquiry::factory()->create(['email' => 'jane@example.com']);

    sendContactInquiryEmails($inquiry);

    Event::assertDispatched(MessageSent::class, fn (MessageSent $event): bool => $event->message->getTo()[0]->getAddress() === 'owner@example.com'
        && $event->message->getReplyTo()[0]->getAddress() === 'jane@example.com');
    Event::assertDispatched(MessageSent::class, fn (MessageSent $event): bool => $event->message->getTo()[0]->getAddress() === 'jane@example.com');
});

it('addresses the confirmation to the bare sender email without their name', function () {
    $inquiry = ContactInquiry::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

    sendContactInquiryEmails($inquiry);

    Event::assertDispatched(MessageSent::class, fn (MessageSent $event): bool => $event->message->getTo()[0]->getAddress() === 'owner@example.com'
        && $event->message->getReplyTo()[0]->getName() === 'Jane Doe');
    Event::assertDispatched(MessageSent::class, fn (MessageSent $event): bool => $event->message->getTo()[0]->getAddress() === 'jane@example.com'
        && $event->message->getTo()[0]->getName() === '');
});

it('retries only the email that failed', function () {
    $events = Event::fake([MessageSent::class]);
    Event::listen(MessageSending::class, function (MessageSending $event): void {
        if ($event->message->getSubject() === 'Thanks for getting in touch') {
            throw new RuntimeException('Simulated mail outage.');
        }
    });
    $inquiry = ContactInquiry::factory()->create();

    expect(fn () => sendContactInquiryEmails($inquiry))
        ->toThrow(RuntimeException::class, 'Contact email delivery is incomplete.');

    $inquiry->refresh();
    $notificationSentAt = $inquiry->notification_sent_at;
    expect($notificationSentAt)
        ->not->toBeNull()
        ->and($inquiry->confirmation_sent_at)
        ->toBeNull();

    $events->dispatcher->forget(MessageSending::class);
    sendContactInquiryEmails($inquiry);

    $inquiry->refresh();
    expect($inquiry->notification_sent_at)
        ->toEqual($notificationSentAt)
        ->and($inquiry->confirmation_sent_at)
        ->not->toBeNull();
    Event::assertDispatchedTimes(MessageSent::class, 2);
});

it('does not record cancelled emails as sent', function () {
    Event::listen(MessageSending::class, fn (): bool => false);
    $inquiry = ContactInquiry::factory()->create();

    expect(fn () => sendContactInquiryEmails($inquiry))
        ->toThrow(RuntimeException::class, 'Contact email delivery is incomplete.');

    $inquiry->refresh();
    expect($inquiry->email_attempted_at)
        ->not->toBeNull()
        ->and($inquiry->notification_sent_at)
        ->toBeNull()
        ->and($inquiry->confirmation_sent_at)
        ->toBeNull();
    Event::assertNotDispatched(MessageSent::class);
});

it('ignores an inquiry that no longer exists', function () {
    new SendContactInquiryEmails(999)
        ->handle(app(Mailer::class));

    Event::assertNotDispatched(MessageSent::class);
});

it('requires manual review instead of resending an inquiry older than 23 hours', function () {
    $inquiry = ContactInquiry::factory()->create(['created_at' => now()->subDay()]);
    $job = new SendContactInquiryEmails($inquiry->id)
        ->withFakeQueueInteractions();

    $job->handle(app(Mailer::class));

    $job->assertFailedWith(new RuntimeException('Contact delivery requires manual review after 23 hours.'));
    Event::assertNotDispatched(MessageSent::class);
});

it('releases the job while another worker holds the inquiry lock', function () {
    $inquiry = ContactInquiry::factory()->create();
    $lock = Cache::lock("contact-emails:{$inquiry->id}", 120);
    $lock->get();
    $job = new SendContactInquiryEmails($inquiry->id)
        ->withFakeQueueInteractions();

    try {
        $job->middleware()[0]->handle($job, fn (SendContactInquiryEmails $job) => $job->handle(app(Mailer::class)));

        $job->assertReleased(60);
        $inquiry->refresh();
        expect($inquiry->email_attempted_at)
            ->toBeNull();
    } finally {
        $lock->release();
    }
});

it('stores the job on the database queue with retry and timeout settings', function () {
    DB::transaction(fn () => dispatch(new SendContactInquiryEmails(42))->beforeCommit());
    $payload = DB::table('jobs')
        ->value('payload');

    if (! is_string($payload)) {
        throw new UnexpectedValueException('The contact job was not stored on the database queue.');
    }

    new AssertableJsonString($payload)
        ->assertPath('maxTries', 3)
        ->assertPath('timeout', 60)
        ->assertPath('failOnTimeout', true)
        ->assertPath('backoff', '60,300,900');
});

it('gives each email a stable provider idempotency key', function () {
    $inquiry = ContactInquiry::factory()->create();

    $notification = new ContactInquiryReceivedMail($inquiry)
        ->headers()
        ->text;
    $confirmation = new ContactConfirmationMail($inquiry)
        ->headers()
        ->text;
    $notificationAgain = new ContactInquiryReceivedMail($inquiry->refresh())
        ->headers()
        ->text;

    expect($notification)
        ->toBe($notificationAgain)
        ->and($notification)
        ->not->toBe($confirmation);
});

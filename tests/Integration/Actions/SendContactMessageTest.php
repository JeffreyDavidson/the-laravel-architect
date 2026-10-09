<?php

use App\Data\ContactMessageData;
use App\Enums\ContactBudget;
use App\Enums\ContactType;
use App\Models\ContactInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use JeffreyDavidson\CreatorKit\Actions\SendContactMessage;
use JeffreyDavidson\CreatorKit\Enums\ContactInquiryStatus;
use JeffreyDavidson\CreatorKit\Jobs\SendContactInquiryEmails;

use function Pest\Laravel\assertDatabaseCount;

pest()->use(RefreshDatabase::class);

it('rolls back the inquiry when its email job cannot be queued and allows a clean retry', function () {
    config()->set('queue.default', 'database');
    $inserts = 0;
    DB::connection()->beforeExecuting(function (string $query) use (&$inserts): void {
        if (str_starts_with($query, 'insert into "jobs"') && ++$inserts === 1) {
            throw new RuntimeException('Synthetic queue failure.');
        }
    });
    $data = new ContactMessageData('Jane Doe', 'jane@example.com', ContactType::Consulting, null, 'Audit request.');

    expect(fn () => app(SendContactMessage::class)->handle($data->toAttributes()))
        ->toThrow(RuntimeException::class, 'Synthetic queue failure.');

    assertDatabaseCount('contact_inquiries', 0);
    assertDatabaseCount('jobs', 0);

    app(SendContactMessage::class)
        ->handle($data->toAttributes());

    assertDatabaseCount('contact_inquiries', 1);
    assertDatabaseCount('jobs', 1);
});

it('rejects a separate queue database before saving a contact inquiry', function () {
    config()->set([
        'queue.connections.database.connection' => 'separate',
        'database.connections.separate' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
    ]);
    $data = new ContactMessageData('Jane Doe', 'jane@example.com', ContactType::Consulting, null, 'Audit request.');

    expect(fn () => app(SendContactMessage::class)->handle($data->toAttributes()))
        ->toThrow(LogicException::class, 'Contact notifications must share the application database.');

    assertDatabaseCount('contact_inquiries', 0);
});

it('saves the inquiry and queues one email job that carries no contact details', function () {
    app(SendContactMessage::class)
        ->handle(new ContactMessageData(
            name: 'Jane Doe',
            email: 'jane@example.com',
            type: ContactType::Consulting,
            budget: ContactBudget::Medium,
            message: 'Can you help with an audit?',
            projectTitle: 'The Laravel Architect',
        )->toAttributes());

    $inquiry = ContactInquiry::query()->sole();
    expect($inquiry)
        ->name->toBe('Jane Doe')
        ->email->toBe('jane@example.com')
        ->type->toBe(ContactType::Consulting)
        ->budget->toBe('medium')
        ->message->toBe('Can you help with an audit?')
        ->project_title->toBe('The Laravel Architect')
        ->status->toBe(ContactInquiryStatus::New);

    $payload = DB::table('jobs')
        ->sole()
        ->payload;
    if (! is_string($payload)) {
        throw new RuntimeException('Expected a JSON queue payload.');
    }
    $encryptedCommand = data_get(json_decode($payload, true, flags: JSON_THROW_ON_ERROR), 'data.command');
    if (! is_string($encryptedCommand)) {
        throw new RuntimeException('Expected an encrypted queued command.');
    }
    $command = Crypt::decrypt($encryptedCommand);

    expect($command)
        ->toBeString()
        ->toContain(SendContactInquiryEmails::class)
        ->not->toContain('Jane Doe', 'jane@example.com', 'Can you help with an audit?');
});

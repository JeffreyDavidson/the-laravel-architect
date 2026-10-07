<?php

use App\Enums\ContactBudget;
use App\Enums\ContactInquiryStatus;
use App\Enums\ContactType;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Filament\Resources\ContactInquiries\Pages\EditContactInquiry;
use App\Filament\Resources\ContactInquiries\Pages\ListContactInquiries;
use App\Models\ContactInquiry;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    actingAs(User::factory()->create(['is_admin' => true]));
});

it('renders inquiry details for an authorized administrator', function () {
    $inquiry = ContactInquiry::factory()->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'message' => 'Please review my application.',
    ]);

    livewire(ListContactInquiries::class)
        ->assertOk()
        ->assertSee('Jane Doe')
        ->assertSee('jane@example.com');

    $component = livewire(EditContactInquiry::class, ['record' => $inquiry->getRouteKey()]);

    $component->assertOk();
    $component->assertSchemaStateSet([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'message' => 'Please review my application.',
    ]);
});

it('does not offer search on encrypted sender columns', function (string $column) {
    $component = livewire(ListContactInquiries::class);

    $component->assertTableColumnExists($column, fn (TextColumn $textColumn): bool => ! $textColumn->isSearchable());
})->with([
    'name',
    'email',
]);

it('finds inquiries by the type label shown in the table', function () {
    $consulting = ContactInquiry::factory()->create(['type' => ContactType::Consulting->value]);
    $freelance = ContactInquiry::factory()->create(['type' => ContactType::Freelance->value]);

    $component = livewire(ListContactInquiries::class)->searchTable('code review');

    $component
        ->assertCanSeeTableRecords([$consulting])
        ->assertCanNotSeeTableRecords([$freelance])
        ->assertTableColumnFormattedStateSet('type', 'Consulting / Code Review', $consulting);
});

it('filters inquiries by status', function () {
    $resolved = ContactInquiry::factory()->create(['status' => ContactInquiryStatus::Resolved]);
    $new = ContactInquiry::factory()->create();

    $component = livewire(ListContactInquiries::class)->filterTable('status', ContactInquiryStatus::Resolved);

    $component
        ->assertCanSeeTableRecords([$resolved])
        ->assertCanNotSeeTableRecords([$new]);
});

it('shows the inquiry type and decrypted budget as labels', function () {
    $inquiry = ContactInquiry::factory()->create([
        'type' => ContactType::Consulting->value,
        'budget' => ContactBudget::Small->value,
    ]);

    $component = livewire(EditContactInquiry::class, ['record' => $inquiry->getRouteKey()]);

    $component->assertSchemaStateSet([
        'type' => ContactType::Consulting,
        'budget' => ContactBudget::Small,
    ]);
});

it('updates only inquiry status and private notes', function () {
    $inquiry = ContactInquiry::factory()->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'message' => 'Please review my application.',
    ]);

    livewire(EditContactInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->fillForm([
            'status' => ContactInquiryStatus::InProgress,
            'notes' => 'Replied on 2026-09-15.',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($inquiry->refresh())
        ->name->toBe('Jane Doe')
        ->email->toBe('jane@example.com')
        ->message->toBe('Please review my application.')
        ->status->toBe(ContactInquiryStatus::InProgress)
        ->notes->toBe('Replied on 2026-09-15.');
});

it('does not allow inquiries to be created in the panel', function () {
    $canCreate = ContactInquiryResource::canCreate();
    $pages = array_keys(ContactInquiryResource::getPages());

    expect($canCreate)
        ->toBeFalse()
        ->and($pages)
        ->not
        ->toContain('create');
});

it('counts only new inquiries in the navigation badge', function () {
    ContactInquiry::factory()
        ->count(2)
        ->create();
    ContactInquiry::factory()->create(['status' => ContactInquiryStatus::Resolved]);

    expect(ContactInquiryResource::getNavigationBadge())->toBe('2');
});

<?php

use App\Enums\ContactInquiryStatus;
use App\Filament\Resources\ContactInquiries\Pages\EditContactInquiry;
use App\Filament\Resources\ContactInquiries\Pages\ListContactInquiries;
use App\Models\ContactInquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
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

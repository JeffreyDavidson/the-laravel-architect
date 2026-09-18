<?php

use App\Models\ContactInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('previews expired inquiries without deleting them', function () {
    config()->set('contact.retention_days', 180);
    $inquiry = ContactInquiry::factory()->create(['created_at' => now()->subDays(181)]);

    $this->artisanCommand('model:prune', ['--model' => ContactInquiry::class, '--pretend' => true])
        ->assertSuccessful();

    $this->assertModelExists($inquiry);
});

it('prunes inquiries older than the configured retention period', function () {
    config()->set('contact.retention_days', 180);

    $oldInquiry = ContactInquiry::factory()->create(['created_at' => now()->subDays(181)]);
    $recentInquiry = ContactInquiry::factory()->create(['created_at' => now()->subDays(179)]);

    $this->artisanCommand('model:prune', ['--model' => ContactInquiry::class])
        ->assertSuccessful();

    expect(ContactInquiry::query()->find($oldInquiry->id))->toBeNull()
        ->and(ContactInquiry::query()->find($recentInquiry->id))->not->toBeNull();
});

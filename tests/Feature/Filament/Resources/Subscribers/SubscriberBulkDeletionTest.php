<?php

use App\Enums\SubscriberStatus;
use App\Filament\Resources\Subscribers\Pages\ListSubscribers;
use App\Models\Subscriber;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertModelExists;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    actingAs(User::factory()->create(['is_admin' => true]));
});

it('deletes selected subscribers through the table bulk action', function () {
    $subscribers = Subscriber::factory()
        ->count(2)
        ->create();

    livewire(ListSubscribers::class)
        ->selectTableRecords($subscribers)
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()
            ->bulk());

    expect(Subscriber::query()->whereKey($subscribers->pluck('id'))
        ->count())->toBe(0);
});

it('keeps suppressed subscribers on the do-not-email list when bulk deleting', function () {
    $suppressed = Subscriber::factory()
        ->suppressed()
        ->create(['email' => 'bounced@example.com']);

    livewire(ListSubscribers::class)
        ->filterTable('status', SubscriberStatus::Suppressed->value)
        ->assertCanSeeTableRecords([$suppressed])
        ->selectTableRecords([$suppressed])
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()
            ->bulk());

    assertModelExists($suppressed);
});

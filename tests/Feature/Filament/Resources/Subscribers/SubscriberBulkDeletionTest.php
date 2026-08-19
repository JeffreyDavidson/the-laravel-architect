<?php

use App\Filament\Resources\Subscribers\Pages\ListSubscribers;
use App\Models\Subscriber;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
});

it('deletes selected subscribers through the table bulk action', function () {
    $subscribers = collect([
        Subscriber::query()->create(['email' => 'first-bulk-delete@example.com']),
        Subscriber::query()->create(['email' => 'second-bulk-delete@example.com']),
    ]);

    livewire(ListSubscribers::class)
        ->callTableBulkAction(DeleteBulkAction::class, $subscribers);

    expect(Subscriber::query()->whereKey($subscribers->pluck('id'))->count())->toBe(0);
});

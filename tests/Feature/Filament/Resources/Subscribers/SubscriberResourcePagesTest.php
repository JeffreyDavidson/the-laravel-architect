<?php

use App\Filament\Resources\Subscribers\Pages\ListSubscribers;
use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);
});

it('renders the subscriber list for an authorized user', function () {
    $subscriber = Subscriber::factory()->create();

    livewire(ListSubscribers::class)
        ->assertOk()
        ->assertSee($subscriber->email);
});

it('does not allow subscribers to be created or edited in the panel', function () {
    $canCreate = SubscriberResource::canCreate();
    $pages = array_keys(SubscriberResource::getPages());

    expect($canCreate)
        ->toBeFalse()
        ->and($pages)
        ->toBe(['index']);
});

it('shows each subscriber status and filters by it', function () {
    $active = Subscriber::factory()->create();
    $suppressed = Subscriber::factory()
        ->suppressed()
        ->create();

    livewire(ListSubscribers::class)
        ->assertCanSeeTableRecords([$active])
        ->assertCanNotSeeTableRecords([$suppressed])
        ->filterTable('status', 'suppressed')
        ->assertCanSeeTableRecords([$suppressed])
        ->assertCanNotSeeTableRecords([$active])
        ->assertSee('Suppressed');
});

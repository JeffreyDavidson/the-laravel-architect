<?php

use App\Enums\SubscriberStatus;
use App\Filament\Resources\Subscribers\Pages\ListSubscribers;
use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;

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

it('lists exactly the subscribers whose status badge matches the chosen status filter', function (SubscriberStatus $status, array $expected) {
    $subscribers = [
        'active' => Subscriber::factory()->create(),
        'pending' => Subscriber::factory()
            ->pending()
            ->create(),
        'unsubscribed' => Subscriber::factory()
            ->unsubscribed()
            ->create(),
        'unsubscribed before confirming' => Subscriber::factory()
            ->pending()
            ->unsubscribed()
            ->create(),
        'suppressed' => Subscriber::factory()
            ->suppressed()
            ->create(),
        'suppressed before confirming' => Subscriber::factory()
            ->pending()
            ->create(['suppressed_at' => now()]),
        'suppressed without unsubscribing' => Subscriber::factory()->create(['suppressed_at' => now()]),
    ];
    $visible = Arr::only($subscribers, $expected);

    $component = livewire(ListSubscribers::class)
        ->filterTable('status', $status->value);

    $component
        ->assertCountTableRecords(count($expected))
        ->assertCanSeeTableRecords($visible)
        ->assertCanNotSeeTableRecords(Arr::except($subscribers, $expected));

    foreach ($visible as $subscriber) {
        $component->assertTableColumnStateSet('status', $status, $subscriber);
    }
})->with([
    'active' => [SubscriberStatus::Active, ['active']],
    'pending' => [SubscriberStatus::Pending, ['pending']],
    'unsubscribed' => [SubscriberStatus::Unsubscribed, ['unsubscribed', 'unsubscribed before confirming']],
    'suppressed' => [SubscriberStatus::Suppressed, ['suppressed', 'suppressed before confirming', 'suppressed without unsubscribing']],
]);

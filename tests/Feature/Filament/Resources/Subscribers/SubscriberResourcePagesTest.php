<?php

use App\Filament\Resources\Subscribers\Pages\ListSubscribers;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
});

it('renders the subscriber list for an authorized user', function () {
    $subscriber = Subscriber::query()->create([
        'email' => 'subscriber-page-coverage@example.com',
        'subscribed_at' => now(),
    ]);

    livewire(ListSubscribers::class)
        ->assertOk()
        ->assertSee($subscriber->email);
});

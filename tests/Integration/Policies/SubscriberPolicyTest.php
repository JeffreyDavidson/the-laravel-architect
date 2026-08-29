<?php

use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

it('allows administrators to manage subscriber records without allowing creation or updates', function () {
    $administrator = User::factory()->create(['is_admin' => true]);
    $subscriber = Subscriber::query()->create(['email' => 'subscriber@example.com']);
    $gate = Gate::forUser($administrator);

    expect($gate->allows('viewAny', Subscriber::class))->toBeTrue()
        ->and($gate->allows('view', $subscriber))->toBeTrue()
        ->and($gate->allows('delete', $subscriber))->toBeTrue()
        ->and($gate->allows('deleteAny', Subscriber::class))->toBeTrue()
        ->and($gate->allows('create', Subscriber::class))->toBeFalse()
        ->and($gate->allows('update', $subscriber))->toBeFalse();
});

it('denies subscriber access to non-administrators', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $subscriber = Subscriber::query()->create(['email' => 'subscriber@example.com']);
    $gate = Gate::forUser($user);

    expect($gate->allows('viewAny', Subscriber::class))->toBeFalse()
        ->and($gate->allows('view', $subscriber))->toBeFalse()
        ->and($gate->allows('delete', $subscriber))->toBeFalse()
        ->and($gate->allows('deleteAny', Subscriber::class))->toBeFalse();
});

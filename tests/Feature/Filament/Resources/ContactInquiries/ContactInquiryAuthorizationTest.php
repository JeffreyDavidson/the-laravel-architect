<?php

use App\Models\ContactInquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('only allows administrators to access inquiry records', function () {
    $inquiry = ContactInquiry::factory()->create();
    $administrator = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create(['is_admin' => false]);

    expect($administrator->can('viewAny', ContactInquiry::class))->toBeTrue()
        ->and($administrator->can('view', $inquiry))->toBeTrue()
        ->and($administrator->can('update', $inquiry))->toBeTrue()
        ->and($administrator->can('delete', $inquiry))->toBeTrue()
        ->and($user->can('viewAny', ContactInquiry::class))->toBeFalse()
        ->and($user->can('view', $inquiry))->toBeFalse()
        ->and($user->can('update', $inquiry))->toBeFalse()
        ->and($user->can('delete', $inquiry))->toBeFalse();
});

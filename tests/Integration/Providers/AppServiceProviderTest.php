<?php

use App\Models\User;
use Laravel\Nightwatch\Core;

it('does not send authenticated user names or email addresses to Nightwatch', function () {
    $resolver = app(Core::class)->userDetailsResolver;
    $user = new User([
        'name' => 'Private Administrator',
        'email' => 'private@example.test',
    ]);

    expect($resolver)->toBeCallable()
        ->and($resolver($user))->toBe([]);
});

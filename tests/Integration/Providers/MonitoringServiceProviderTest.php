<?php

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Laravel\Nightwatch\Core;

it('registers pseudonymous authenticated user details with Nightwatch', function () {
    Config::set('app.key', 'private-application-key');
    $resolver = app(Core::class)->userDetailsResolver;
    if ($resolver === null) {
        throw new RuntimeException('Nightwatch user details resolver was not registered.');
    }
    $user = new User;
    $user->forceFill([
        'id' => 42,
        'name' => 'Private Administrator',
        'email' => 'private@example.test',
    ]);

    expect($resolver($user))->toBe([
        'id' => hash_hmac('sha256', '42', 'private-application-key'),
    ]);
});

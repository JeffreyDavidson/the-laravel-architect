<?php

use App\Models\User;
use App\Monitoring\ResolveNightwatchUser;
use Illuminate\Support\Facades\Config;

it('replaces the authenticated user identifier with an application-keyed digest', function () {
    Config::set('app.key', 'private-application-key');
    $user = new User;
    $user->forceFill([
        'id' => 42,
        'name' => 'Private Administrator',
        'email' => 'private@example.test',
    ]);

    $details = app(ResolveNightwatchUser::class)($user);

    expect($details)->toBe([
        'id' => hash_hmac('sha256', '42', 'private-application-key'),
    ])->not->toBe(['id' => '42'])
        ->and(serialize($details))->not->toContain('Private Administrator', 'private@example.test');
});

it('fully redacts the identifier when the application key is unavailable', function () {
    Config::set('app.key', null);
    $user = new User;
    $user->forceFill(['id' => 42]);

    expect(app(ResolveNightwatchUser::class)($user))->toBe([
        'id' => '[redacted]',
    ]);
});

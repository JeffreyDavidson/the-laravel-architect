<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\freezeTime;

pest()->use(RefreshDatabase::class);

it('deletes only expired cache entries', function (int $secondsUntilExpiry, bool $isKept) {
    freezeTime();
    DB::table('cache')->insert([
        'key' => 'rate-limiter-key',
        'value' => 'i:1;',
        'expiration' => now()->getTimestamp() + $secondsUntilExpiry,
    ]);

    $this->artisanCommand('cache:prune-expired')
        ->assertSuccessful();

    $isStillStored = DB::table('cache')
        ->where('key', 'rate-limiter-key')
        ->exists();

    expect($isStillStored)->toBe($isKept);
})->with([
    'expired' => [-60, false],
    'expiring now' => [0, false],
    'not expired' => [60, true],
    'stored forever' => [315360000, true],
]);

it('leaves expired cache locks alone', function () {
    freezeTime();
    DB::table('cache_locks')->insert([
        'key' => 'scheduler-lock',
        'owner' => 'owner',
        'expiration' => now()->getTimestamp() - 60,
    ]);

    $this->artisanCommand('cache:prune-expired')
        ->expectsOutputToContain('Deleted 0 expired cache entries.')
        ->assertSuccessful();

    $isLockStored = DB::table('cache_locks')
        ->where('key', 'scheduler-lock')
        ->exists();

    expect($isLockStored)->toBeTrue();
});

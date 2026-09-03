<?php

use App\Services\DatabaseHealthMonitor;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->originalDatabaseConnection = config('database.default');
    $this->databasePath = tempnam(sys_get_temp_dir(), 'tla-sqlite-health-');

    config()->set([
        'database.default' => 'sqlite-health-test',
        'database.connections.sqlite-health-test' => [
            'driver' => 'sqlite',
            'database' => $this->databasePath,
            'prefix' => '',
            'foreign_key_constraints' => true,
            'busy_timeout' => 5000,
            'journal_mode' => 'WAL',
            'synchronous' => 'NORMAL',
            'transaction_mode' => 'DEFERRED',
        ],
    ]);
});

afterEach(function () {
    DB::purge('sqlite-health-test');
    config()->set('database.default', $this->originalDatabaseConnection);

    if (is_string($this->databasePath) && file_exists($this->databasePath)) {
        unlink($this->databasePath);
    }
});

it('verifies the effective SQLite integrity and connection safeguards', function () {
    app(DatabaseHealthMonitor::class)->ensureHealthy();
})->throwsNoExceptions();

it('rejects an SQLite connection without the concurrency safeguards', function () {
    config()->set([
        'database.connections.sqlite-health-test.busy_timeout' => 1000,
        'database.connections.sqlite-health-test.journal_mode' => 'DELETE',
        'database.connections.sqlite-health-test.synchronous' => 'OFF',
    ]);

    expect(fn () => app(DatabaseHealthMonitor::class)->ensureHealthy())
        ->toThrow(RuntimeException::class, 'The SQLite concurrency safeguards are not active.');
});

<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('configures SQLite for concurrent application workloads', function () {
    expect(config('database.connections.sqlite'))
        ->toMatchArray([
            'busy_timeout' => 5000,
            'journal_mode' => 'WAL',
            'synchronous' => 'NORMAL',
            'transaction_mode' => 'IMMEDIATE',
        ]);
});

it('keeps a read-then-write transaction from failing when another process writes during it', function () {
    $path = tempnam(sys_get_temp_dir(), 'tla-sqlite-lock-');
    $settings = [
        ...config()->array('database.connections.sqlite'),
        'database' => $path,
    ];
    config([
        'database.connections.lock_worker' => $settings,
        'database.connections.lock_scheduler' => [...$settings, 'busy_timeout' => 50],
    ]);
    $worker = DB::connection('lock_worker');
    $scheduler = DB::connection('lock_scheduler');
    $worker->statement('create table jobs (id integer primary key, reserved_at integer)');
    $worker->table('jobs')
        ->insert(['id' => 1]);

    // Mirrors DatabaseQueue::pop(): read the next job, then reserve it, while the
    // scheduler inserts the next job between the read and the reservation.
    $worker->transaction(function () use ($worker, $scheduler): void {
        $worker->table('jobs')
            ->whereNull('reserved_at')
            ->first();

        try {
            $scheduler->table('jobs')
                ->insert(['id' => 2]);
        } catch (QueryException) {
            // With IMMEDIATE the scheduler waits for the worker instead of invalidating it.
        }

        $worker->table('jobs')
            ->where('id', 1)
            ->update(['reserved_at' => 1]);
    });

    $reservedAt = $worker->table('jobs')
        ->where('id', 1)
        ->value('reserved_at');

    expect($reservedAt)->toBe(1);

    DB::purge('lock_worker');
    DB::purge('lock_scheduler');
    unlink($path);
});

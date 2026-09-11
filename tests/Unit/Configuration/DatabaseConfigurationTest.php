<?php

it('configures SQLite for concurrent application workloads', function () {
    expect(config('database.connections.sqlite'))
        ->toMatchArray([
            'busy_timeout' => 5000,
            'journal_mode' => 'WAL',
            'synchronous' => 'NORMAL',
        ]);
});

<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('reports that the application and database are healthy', function () {
    $this->getJson('/up')
        ->assertOk()
        ->assertExactJson(['status' => 'up']);
});

it('reports an unavailable database as unhealthy', function () {
    $defaultConnection = config('database.default');

    config()->set([
        'app.debug' => false,
        'database.default' => 'unavailable',
        'database.connections.unavailable' => [
            'driver' => 'sqlite',
            'database' => '/dev/null/database.sqlite',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ],
    ]);
    DB::purge('unavailable');

    $response = $this->getJson('/up');

    DB::purge('unavailable');
    config()->set('database.default', $defaultConnection);

    $response
        ->assertStatus(500)
        ->assertExactJson(['status' => 'down']);
});

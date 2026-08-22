<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('accepts a failed-job count at the configured threshold', function () {
    config()->set('health.failed_jobs.alert_threshold', 0);

    $this->artisan('app:monitor-failed-jobs')
        ->expectsOutput('Failed-job count is healthy: 0 retained.')
        ->assertSuccessful();
});

it('fails when retained failed jobs exceed the configured threshold', function () {
    config()->set('health.failed_jobs.alert_threshold', 0);
    DB::table('failed_jobs')->insert([
        'uuid' => (string) Str::uuid(),
        'connection' => 'database',
        'queue' => 'default',
        'payload' => '{}',
        'exception' => 'Test failure',
        'failed_at' => now(),
    ]);

    $this->artisan('app:monitor-failed-jobs')
        ->expectsOutput('Failed jobs exceed the configured threshold: 1 retained.')
        ->assertFailed();
});

it('rejects an invalid failed-job threshold', function () {
    config()->set('health.failed_jobs.alert_threshold', -1);

    $this->artisan('app:monitor-failed-jobs')
        ->expectsOutput('Failed-job alert threshold is invalid.')
        ->assertFailed();
});

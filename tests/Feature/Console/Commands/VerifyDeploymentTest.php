<?php

use App\Support\RuntimeHealth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set([
        'backup.backup.name' => 'deployment-test',
        'backup.backup.destination.disks' => ['deployment-backups'],
        'filesystems.disks.deployment-backups' => [
            'driver' => 'local',
            'root' => storage_path('framework/testing/disks/deployment-backups'),
        ],
        'health.backup.max_age_hours' => 36,
        'health.runtime.max_age_seconds' => 300,
    ]);

    Storage::fake('deployment-backups');
    Storage::disk('deployment-backups')->put('deployment-test/fresh.zip', 'backup');
    Cache::put(RuntimeHealth::SCHEDULER_HEARTBEAT_KEY, now()->getTimestamp());
    Cache::put(RuntimeHealth::QUEUE_HEARTBEAT_KEY, now()->getTimestamp());
});

it('accepts a healthy deployment at the expected commit', function () {
    Process::fake(fn () => Process::result("expected-commit\n"));

    $this->artisan('app:verify-deployment', ['commit' => 'expected-commit'])
        ->expectsOutput('Deployment verification passed.')
        ->assertSuccessful();
});

it('reports a mismatched deployment without exposing either commit', function () {
    Process::fake(fn () => Process::result("deployed-commit\n"));

    $this->artisan('app:verify-deployment', ['commit' => 'expected-commit'])
        ->expectsOutput('Deployment verification failed:')
        ->expectsOutputToContain('The deployed Git commit does not match the expected release.')
        ->doesntExpectOutput('deployed-commit')
        ->doesntExpectOutput('expected-commit')
        ->assertFailed();
});

it('reports pending database migrations', function () {
    Process::fake(fn () => Process::result("expected-commit\n"));
    $latestMigration = DB::table('migrations')->orderByDesc('id')->value('migration');
    DB::table('migrations')->where('migration', $latestMigration)->delete();

    $this->artisan('app:verify-deployment', ['commit' => 'expected-commit'])
        ->expectsOutputToContain('The application has pending database migrations.')
        ->assertFailed();
});

it('reports stale runtime heartbeats and missing backups', function () {
    Process::fake(fn () => Process::result("expected-commit\n"));
    Cache::put(RuntimeHealth::QUEUE_HEARTBEAT_KEY, now()->subMinutes(10)->getTimestamp());
    Storage::disk('deployment-backups')->delete('deployment-test/fresh.zip');

    $this->artisan('app:verify-deployment', ['commit' => 'expected-commit'])
        ->expectsOutputToContain('The scheduler or queue worker heartbeat is stale.')
        ->expectsOutputToContain('One or more backup destinations do not contain a fresh backup.')
        ->assertFailed();
});

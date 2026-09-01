<?php

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Services\NightwatchHealthMonitor;
use App\Services\RuntimeHealthMonitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use JMac\Testing\Double;

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
        'nightwatch.deployment' => 'expected-commit',
    ]);

    Storage::fake('deployment-backups');
    Storage::fake('public');
    Storage::disk('deployment-backups')->put('deployment-test/fresh.zip', 'backup');
    Cache::put(RuntimeHealthMonitor::SCHEDULER_HEARTBEAT_KEY, now()->getTimestamp());
    Cache::put(RuntimeHealthMonitor::QUEUE_HEARTBEAT_KEY, now()->getTimestamp());

    $nightwatch = Double::for(NightwatchHealthMonitor::class);
    $nightwatch->allows('ensureHealthy');
    app()->instance(NightwatchHealthMonitor::class, $nightwatch);
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
    Cache::put(RuntimeHealthMonitor::QUEUE_HEARTBEAT_KEY, now()->subMinutes(10)->getTimestamp());
    Storage::disk('deployment-backups')->delete('deployment-test/fresh.zip');

    $this->artisan('app:verify-deployment', ['commit' => 'expected-commit'])
        ->expectsOutputToContain('The scheduler or queue worker heartbeat is stale.')
        ->expectsOutputToContain('One or more backup destinations do not contain a fresh backup.')
        ->assertFailed();
});

it('reports an unavailable Nightwatch agent without exposing its error', function () {
    Process::fake(fn () => Process::result("expected-commit\n"));
    $nightwatch = Double::for(NightwatchHealthMonitor::class);
    $nightwatch->expects('ensureHealthy')
        ->throws(new RuntimeException('private ingest address'));
    app()->instance(NightwatchHealthMonitor::class, $nightwatch);

    $this->artisan('app:verify-deployment', ['commit' => 'expected-commit'])
        ->expectsOutputToContain('The Nightwatch agent is unavailable.')
        ->doesntExpectOutput('private ingest address')
        ->assertFailed();
});

it('reports mismatched Nightwatch deployment metadata without exposing either identifier', function () {
    Process::fake(fn () => Process::result("expected-commit\n"));
    config()->set('nightwatch.deployment', 'previous-commit');

    $this->artisan('app:verify-deployment', ['commit' => 'expected-commit'])
        ->expectsOutputToContain('Nightwatch is not configured with the expected deployment identifier.')
        ->doesntExpectOutput('previous-commit')
        ->doesntExpectOutput('expected-commit')
        ->assertFailed();
});

it('reports incomplete responsive media without exposing its path', function () {
    Process::fake(fn () => Process::result("expected-commit\n"));
    $image = UploadedFile::fake()->image('private-project-name.png', 1280, 72);
    Storage::disk('public')->put('projects/private-project-name.png', $image->getContent());

    Project::withoutEvents(fn () => Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => ProjectStatus::Published,
        'featured_image_path' => 'projects/private-project-name.png',
    ]));

    $this->artisan('app:verify-deployment', ['commit' => 'expected-commit'])
        ->expectsOutputToContain('One or more stored images are missing required responsive variants.')
        ->doesntExpectOutputToContain('private-project-name.png')
        ->assertFailed();
});

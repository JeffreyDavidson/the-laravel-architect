<?php

use App\Enums\PublishStatus;
use App\Models\Project;
use App\Support\Monitoring\Health\NightwatchHealthMonitor;
use App\Support\Monitoring\Health\RuntimeHealthMonitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use JMac\Testing\Double;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    config()->set([
        'backup.backup.name' => 'deployment-test',
        'backup.backup.destination.disks' => ['deployment-backups'],
        'filesystems.disks.deployment-backups' => [
            'driver' => 'local',
            'root' => storage_path('framework/testing/disks/deployment-backups'),
        ],
        'health.runtime.max_age_seconds' => 300,
        'app.deployment_environment' => 'production',
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

    $this->artisanCommand('app:verify-deployment', ['commit' => 'expected-commit'])
        ->expectsOutput('Deployment verification passed.')
        ->assertSuccessful();
});

it('reports a mismatched deployment without exposing either commit', function () {
    Process::fake(fn () => Process::result("deployed-commit\n"));

    $this->artisanCommand('app:verify-deployment', ['commit' => 'expected-commit'])
        ->expectsOutput('Deployment verification failed:')
        ->expectsOutputToContain('The deployed Git commit does not match the expected release.')
        ->doesntExpectOutput('deployed-commit')
        ->doesntExpectOutput('expected-commit')
        ->assertFailed();
});

it('reports pending database migrations', function () {
    Process::fake(fn () => Process::result("expected-commit\n"));
    $migrationQuery = DB::table('migrations');
    $migrationQuery->orderByDesc('id');
    $latestMigration = $migrationQuery->value('migration');
    $migrationQuery = DB::table('migrations');
    $migrationQuery->where('migration', $latestMigration);
    $migrationQuery->delete();

    $this->artisanCommand('app:verify-deployment', ['commit' => 'expected-commit'])
        ->expectsOutputToContain('The application has pending database migrations.')
        ->assertFailed();
});

it('reports stale runtime heartbeats', function () {
    Process::fake(fn () => Process::result("expected-commit\n"));
    $staleHeartbeat = now()->subMinutes(10);
    Cache::put(RuntimeHealthMonitor::QUEUE_HEARTBEAT_KEY, $staleHeartbeat->getTimestamp());
    Storage::disk('deployment-backups')->delete('deployment-test/fresh.zip');

    $this->artisanCommand('app:verify-deployment', ['commit' => 'expected-commit'])
        ->expectsOutputToContain('The scheduler or queue worker heartbeat is stale.')
        ->assertFailed();
});

it('does not use backup freshness as a release gate', function () {
    Process::fake(fn () => Process::result("expected-commit\n"));
    Storage::disk('deployment-backups')->delete('deployment-test/fresh.zip');

    $this->artisanCommand('app:verify-deployment', ['commit' => 'expected-commit'])
        ->expectsOutput('Deployment verification passed.')
        ->assertSuccessful();
});

it('reports an unavailable Nightwatch agent without exposing its error', function () {
    Process::fake(fn () => Process::result("expected-commit\n"));
    $nightwatch = Double::for(NightwatchHealthMonitor::class);
    $nightwatch->expects('ensureHealthy')
        ->throws(new RuntimeException('private ingest address'));
    app()->instance(NightwatchHealthMonitor::class, $nightwatch);

    $this->artisanCommand('app:verify-deployment', ['commit' => 'expected-commit'])
        ->expectsOutputToContain('The Nightwatch agent is unavailable.')
        ->doesntExpectOutput('private ingest address')
        ->assertFailed();
});

it('reports mismatched Nightwatch deployment metadata without exposing either identifier', function () {
    Process::fake(fn () => Process::result("expected-commit\n"));
    config()->set('nightwatch.deployment', 'previous-commit');

    $this->artisanCommand('app:verify-deployment', ['commit' => 'expected-commit'])
        ->expectsOutputToContain('Nightwatch is not configured with the expected deployment identifier.')
        ->doesntExpectOutput('previous-commit')
        ->doesntExpectOutput('expected-commit')
        ->assertFailed();
});

it('does not require Nightwatch for staging deployments', function () {
    Process::fake(fn () => Process::result("expected-commit\n"));
    config()->set('app.deployment_environment', 'staging');
    $nightwatch = Double::for(NightwatchHealthMonitor::class);
    $nightwatch->expects('ensureHealthy')->never();
    app()->instance(NightwatchHealthMonitor::class, $nightwatch);

    $this->artisanCommand('app:verify-deployment', ['commit' => 'expected-commit'])
        ->expectsOutput('Deployment verification passed.')
        ->assertSuccessful();
});

it('does not use existing incomplete responsive media as a release gate', function () {
    Process::fake(fn () => Process::result("expected-commit\n"));
    $image = UploadedFile::fake()->image('private-project-name.png', 1280, 72);
    $imageContents = $image->getContent();
    Storage::disk('public')->put('projects/private-project-name.png', $imageContents);

    Project::withoutEvents(fn () => Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => PublishStatus::Published,
        'featured_image_path' => 'projects/private-project-name.png',
    ]));

    $this->artisanCommand('app:verify-deployment', ['commit' => 'expected-commit'])
        ->expectsOutput('Deployment verification passed.')
        ->doesntExpectOutputToContain('private-project-name.png')
        ->assertSuccessful();
});

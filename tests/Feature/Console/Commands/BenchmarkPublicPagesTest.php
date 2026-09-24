<?php

use App\Services\ScaleTestContentWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('reports first and steady request metrics for the representative public pages', function () {
    app()->detectEnvironment(fn (): string => 'local');
    app(ScaleTestContentWorkflow::class)->seed();

    $this->artisanCommand('content:benchmark', ['--iterations' => 2])
        ->expectsOutputToContain('/blog')
        ->expectsOutputToContain('/podcasts/scale-test-podcast')
        ->expectsOutputToContain('/projects')
        ->expectsOutputToContain('Steady avg queries')
        ->assertSuccessful();
});

it('rejects benchmark runs outside the local environment', function () {
    app()->detectEnvironment(fn (): string => 'production');

    $this->artisanCommand('content:benchmark')
        ->expectsOutput('Public page benchmarking is limited to the local environment.')
        ->assertFailed();
});

it('fails when a documented benchmark page does not respond successfully', function () {
    app()->detectEnvironment(fn (): string => 'local');

    $this->artisanCommand('content:benchmark', ['--iterations' => 2])
        ->expectsOutputToContain('Benchmark request for /podcasts/scale-test-podcast returned HTTP 404.')
        ->assertFailed();
});

it('rejects iteration counts outside the bounded range', function (string $iterations) {
    app()->detectEnvironment(fn (): string => 'local');

    $this->artisanCommand('content:benchmark', ['--iterations' => $iterations])
        ->expectsOutput('The iteration count must be an integer between 2 and 25.')
        ->assertExitCode(2);
})->with(['1', '26', 'not-a-number']);

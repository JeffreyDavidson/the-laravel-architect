<?php

use App\Services\PublicPageBenchmark;
use App\Services\ScaleTestContentWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('counts each query once across repeated benchmark runs', function () {
    app()->detectEnvironment(fn (): string => 'local');
    app(ScaleTestContentWorkflow::class)->seed();

    $firstRun = app(PublicPageBenchmark::class)->measure(2);
    $secondRun = app(PublicPageBenchmark::class)->measure(2);

    expect(array_column(array_column($firstRun, 'first'), 'queries'))
        ->toBe(array_column(array_column($secondRun, 'first'), 'queries'));
});

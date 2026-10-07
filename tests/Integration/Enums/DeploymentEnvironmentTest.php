<?php

use App\Enums\DeploymentEnvironment;

covers(DeploymentEnvironment::class);

it('resolves the configured deployment environment', function (mixed $configured, ?DeploymentEnvironment $expected) {
    config()->set('app.deployment_environment', $configured);

    $environment = DeploymentEnvironment::current();

    expect($environment)
        ->toBe($expected);
})->with([
    'production' => ['production', DeploymentEnvironment::Production],
    'staging' => ['staging', DeploymentEnvironment::Staging],
    'local' => ['local', null],
    'unknown' => ['preview', null],
    'wrong case' => ['Production', null],
    'empty' => ['', null],
    'missing' => [null, null],
]);

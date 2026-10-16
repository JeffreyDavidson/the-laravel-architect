<?php

use App\Services\PublicPageBenchmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

/*
 * Services take the values they need (a token, a client IP) from their caller instead of reading
 * the current HTTP request. PublicPageBenchmark builds its own requests to send through the kernel.
 */
arch('keeps the current HTTP request out of services')
    ->expect('App\Services')
    ->not->toUse(Request::class)
    ->ignoring(PublicPageBenchmark::class);

arch('names long operations Workflow rather than Synchronizer, Manager or Processor')
    ->expect('App\Services')
    ->classes()
    ->not->toHaveSuffix('Synchronizer')
    ->not->toHaveSuffix('Manager')
    ->not->toHaveSuffix('Processor');

it('constructor-injects workflow collaborators instead of taking callables', function () {
    $workflows = collect(File::allFiles(app_path('Services')))
        ->map(fn (SplFileInfo $file): string => 'App\\Services\\'.str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname()))
        ->filter(fn (string $class): bool => str_ends_with($class, 'Workflow'));

    expect($workflows)->not->toBeEmpty();

    foreach ($workflows as $class) {
        if (! class_exists($class)) {
            throw new RuntimeException("Unable to load workflow class {$class}.");
        }

        $constructor = new ReflectionClass($class)->getConstructor();
        $parameters = $constructor?->getParameters() ?? [];

        foreach ($parameters as $parameter) {
            $type = (string) $parameter->getType();

            expect(str_contains($type, 'Closure') || str_contains($type, 'callable'))
                ->toBeFalse("{$class} takes \${$parameter->getName()} as a callable; constructor-inject the collaborator instead.");
        }
    }
});

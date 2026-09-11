<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

arch('keeps SEO metadata construction outside controllers')
    ->expect('App\Http\Controllers')
    ->not->toUse('RalphJSmit\Laravel\SEO\Support\SEOData');

$controllerClasses = function (): array {
    $directory = dirname(__DIR__, 2).'/app/Http/Controllers';

    return collect(File::allFiles($directory))
        ->map(function (SplFileInfo $file) use ($directory): string {
            $relativePath = Str::after(
                $file->getPathname(),
                $directory.DIRECTORY_SEPARATOR,
            );

            return 'App\\Http\\Controllers\\'.Str::of($relativePath)
                ->replace(['/', '\\'], '\\')
                ->beforeLast('.php')
                ->toString();
        })
        ->sort()
        ->values()
        ->all();
};

it('prohibits private methods in controllers', function () use ($controllerClasses) {
    foreach ($controllerClasses() as $controller) {
        if (! class_exists($controller)) {
            throw new RuntimeException("Controller class {$controller} could not be loaded.");
        }

        $reflection = new ReflectionClass($controller);
        $privateMethods = collect($reflection->getMethods(ReflectionMethod::IS_PRIVATE))
            ->filter(fn (ReflectionMethod $method): bool => $method->getDeclaringClass()->getName() === $controller)
            ->map(fn (ReflectionMethod $method): string => $method->getName())
            ->values()
            ->all();

        expect($privateMethods)->toBe([], "{$controller} may not declare private methods.");
    }
});

it('keeps controllers invokable or resourceful', function () use ($controllerClasses) {
    $resourceMethods = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];

    foreach ($controllerClasses() as $controller) {
        if (! class_exists($controller)) {
            throw new RuntimeException("Controller class {$controller} could not be loaded.");
        }

        $reflection = new ReflectionClass($controller);
        $publicMethods = collect($reflection->getMethods(ReflectionMethod::IS_PUBLIC))
            ->filter(fn (ReflectionMethod $method): bool => $method->getDeclaringClass()->getName() === $controller)
            ->reject(fn (ReflectionMethod $method): bool => $method->isConstructor())
            ->map(fn (ReflectionMethod $method): string => $method->getName())
            ->sort()
            ->values()
            ->all();

        if ($reflection->isAbstract()) {
            expect($publicMethods)->toBe([], "{$controller} may not define controller actions.");

            continue;
        }

        if (in_array('__invoke', $publicMethods, true)) {
            expect($publicMethods)
                ->toBe(['__invoke'], "{$controller} must contain only its __invoke action.");

            continue;
        }

        expect($publicMethods)->not->toBe([], "{$controller} must define a controller action.")
            ->and(
                array_values(array_diff($publicMethods, $resourceMethods)),
            )->toBe([], "{$controller} contains non-resource controller actions.");
    }
});

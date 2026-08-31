<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

$controllerClasses = function (): array {
    $directory = dirname(__DIR__, 4).'/app/Http/Controllers';

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
        $reflection = new ReflectionClass($controller);
        $privateMethods = collect($reflection->getMethods(ReflectionMethod::IS_PRIVATE))
            ->filter(fn (ReflectionMethod $method): bool => $method->getDeclaringClass()->getName() === $controller)
            ->map(fn (ReflectionMethod $method): string => $method->getName())
            ->values()
            ->all();

        expect($privateMethods, "{$controller} may not declare private methods.")->toBe([]);
    }
});

it('keeps controllers invokable or resourceful', function () use ($controllerClasses) {
    $resourceMethods = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];

    foreach ($controllerClasses() as $controller) {
        $reflection = new ReflectionClass($controller);
        $publicMethods = collect($reflection->getMethods(ReflectionMethod::IS_PUBLIC))
            ->filter(fn (ReflectionMethod $method): bool => $method->getDeclaringClass()->getName() === $controller)
            ->reject(fn (ReflectionMethod $method): bool => $method->isConstructor())
            ->map(fn (ReflectionMethod $method): string => $method->getName())
            ->sort()
            ->values()
            ->all();

        if ($reflection->isAbstract()) {
            expect($publicMethods, "{$controller} may not define controller actions.")->toBe([]);

            continue;
        }

        if (in_array('__invoke', $publicMethods, true)) {
            expect($publicMethods, "{$controller} must contain only its __invoke action.")
                ->toBe(['__invoke']);

            continue;
        }

        expect($publicMethods, "{$controller} must define a controller action.")->not->toBe([])
            ->and(
                array_values(array_diff($publicMethods, $resourceMethods)),
                "{$controller} contains non-resource controller actions.",
            )->toBe([]);
    }
});

<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

it('requires actions to expose handle instead of invoke', function () {
    $directory = app_path('Actions');

    foreach (File::allFiles($directory) as $file) {
        $relativePath = Str::after($file->getPathname(), $directory.DIRECTORY_SEPARATOR);
        $class = 'App\\Actions\\'.Str::of($relativePath)
            ->replace(['/', '\\'], '\\')
            ->beforeLast('.php')
            ->toString();

        if (! class_exists($class)) {
            throw new RuntimeException("Unable to load action class {$class}.");
        }

        $reflection = new ReflectionClass($class);

        expect($reflection->hasMethod('__invoke'))->toBeFalse("{$class} must not be invokable.")
            ->and($reflection->hasMethod('handle'))->toBeTrue("{$class} must define handle().");

        $handle = $reflection->getMethod('handle');

        expect($handle->isPublic())->toBeTrue("{$class}::handle must be public.")
            ->and($handle->isStatic())->toBeFalse("{$class}::handle must be an instance method.");
    }
});

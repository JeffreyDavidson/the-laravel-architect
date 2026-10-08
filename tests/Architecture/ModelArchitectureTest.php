<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RalphJSmit\Laravel\SEO\Support\SEOData;

arch('keeps models independent of application services')
    ->expect('App\Models')
    ->not->toUse('App\Services');

arch('keeps stored-file URLs out of models')
    ->expect('App\Models')
    ->not->toUse(Storage::class);

arch('leaves URLs, presenters and page SEO to the view layer')
    ->expect('App\Models')
    ->not->toUse([
        'App\Presenters',
        'route',
        'url',
        SEOData::class,
    ]);

it('keeps route binding concerns outside Eloquent models', function () {
    $directory = app_path('Models');

    foreach (File::allFiles($directory) as $file) {
        $relativePath = Str::after($file->getPathname(), $directory.DIRECTORY_SEPARATOR);
        $class = 'App\\Models\\'.Str::of($relativePath)
            ->replace(['/', '\\'], '\\')
            ->beforeLast('.php')
            ->toString();

        if (trait_exists($class) || interface_exists($class) || enum_exists($class)) {
            continue;
        }

        if (! class_exists($class)) {
            throw new RuntimeException("Unable to load model class {$class}.");
        }

        if (! is_subclass_of($class, Model::class)) {
            continue;
        }

        $reflection = new ReflectionClass($class);
        $routeBinding = $reflection->getMethod('resolveRouteBinding');

        expect($routeBinding->getDeclaringClass()
            ->getName())
            ->not->toBe($class, "{$class} must not declare HTTP route binding logic.");
    }
});

<?php

use Illuminate\Console\Attributes\Signature;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

it('keeps the Feature suite organized by responsibility', function () {
    expect(File::files(base_path('tests/Feature')))->toBe([]);
});

it('keeps a one-to-one test mapping for application console commands', function () {
    $commandDirectory = app_path('Console/Commands');
    $testDirectories = [
        base_path('tests/Feature/Console/Commands'),
        base_path('tests/Integration/Console/Commands'),
    ];
    $testFiles = collect($testDirectories)
        ->flatMap(fn (string $directory): array => File::exists($directory) ? File::allFiles($directory) : [])
        ->map(fn (SplFileInfo $file): string => $file->getFilename())
        ->values();

    foreach (File::files($commandDirectory) as $file) {
        $className = Str::beforeLast($file->getFilename(), '.php');
        $baseName = Str::beforeLast($className, 'Test');
        $expectedTestNames = array_unique([
            "{$className}CommandTest.php",
            "{$className}Test.php",
            "{$baseName}CommandTest.php",
        ]);
        $matchingTests = $testFiles->filter(fn (string $testFile): bool => in_array($testFile, $expectedTestNames, true));

        expect($matchingTests)->toHaveCount(1, "{$className} must have exactly one matching command test.");

        $class = "App\\Console\\Commands\\{$className}";
        $signatures = (new ReflectionClass($class))->getAttributes(Signature::class);

        expect($signatures)->toHaveCount(1, "{$class} must declare one command signature.");
    }
});

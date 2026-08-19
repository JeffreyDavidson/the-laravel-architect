<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

it('generates all thumbnails in an isolated output directory', function () {
    $outputDirectory = storage_path('framework/testing/thumbnails-'.Str::uuid());
    $filenames = [
        'yt-thumb-testing.png',
        'yt-thumb-saas.png',
        'yt-thumb-codeigniter.png',
    ];

    try {
        $this->artisan('thumbnails:generate', ['--output-dir' => $outputDirectory])
            ->expectsOutput('Generated: yt-thumb-testing.png')
            ->expectsOutput('Generated: yt-thumb-saas.png')
            ->expectsOutput('Generated: yt-thumb-codeigniter.png')
            ->expectsOutput('All thumbnails generated!')
            ->assertSuccessful();

        foreach ($filenames as $filename) {
            $path = $outputDirectory.DIRECTORY_SEPARATOR.$filename;
            $dimensions = getimagesize($path);

            expect(File::exists($path))->toBeTrue()
                ->and(File::size($path))->toBeGreaterThan(0)
                ->and($dimensions)->not->toBeFalse()
                ->and($dimensions[0])->toBe(1280)
                ->and($dimensions[1])->toBe(720)
                ->and($dimensions['mime'])->toBe('image/png');
        }
    } finally {
        File::deleteDirectory($outputDirectory);
    }
});

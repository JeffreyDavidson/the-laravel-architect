<?php

use App\Support\Content\Archives\PublicContentArchive;
use Illuminate\Support\Facades\File;
use JMac\Testing\Double;

it('exports the public archive to the requested absolute path', function () {
    $archive = Double::for(PublicContentArchive::class);
    $archive->expects('export')->resolves(fn (): array => [
        'version' => 1,
        'posts' => [['slug' => 'published-post']],
    ]);
    app()->instance(PublicContentArchive::class, $archive);

    $directory = sys_get_temp_dir().'/the-laravel-architect-tests';
    $path = $directory.'/exports/public-content.json';
    File::deleteDirectory($directory);

    try {
        $this->artisanCommand('content:export-public', ['path' => $path])
            ->expectsOutput('Public-content archive written.')
            ->assertSuccessful();

        expect(File::json($path))->toMatchArray([
            'version' => 1,
            'posts' => [['slug' => 'published-post']],
        ]);
    } finally {
        File::deleteDirectory($directory);
    }
});

it('rejects a relative export path', function () {
    $this->artisanCommand('content:export-public', ['path' => 'storage/public-content.json'])
        ->expectsOutput('The public-content archive path must be absolute.')
        ->assertFailed();
});

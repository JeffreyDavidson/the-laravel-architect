<?php

use App\Content\PublicContentArchive;
use Illuminate\Support\Facades\File;
use JMac\Testing\Double;

it('imports the archive and reports synchronized record counts', function () {
    $decoded = [
        'version' => 1,
        'categories' => [],
        'posts' => [],
        'projects' => [],
        'podcasts' => [],
        'episodes' => [],
        'videos' => [],
    ];
    $archive = Double::for(PublicContentArchive::class);
    $archive->expects('decode')->with('{}')->resolves(fn (): array => $decoded);
    $archive->expects('sync')->with($decoded)->resolves(fn (): array => [
        'categories' => 2,
        'posts' => 1,
        'projects' => 0,
        'podcasts' => 0,
        'episodes' => 0,
        'videos' => 3,
    ]);
    app()->instance(PublicContentArchive::class, $archive);

    $path = tempnam(sys_get_temp_dir(), 'public-content-');
    if ($path === false) {
        throw new RuntimeException('Unable to create a temporary archive path.');
    }

    File::put($path, '{}');

    try {
        $this->artisanCommand('content:import-public', ['path' => $path])
            ->expectsOutput('2 categories, 1 posts, 0 projects, 0 podcasts, 0 episodes, 3 videos synchronized.')
            ->assertSuccessful();
    } finally {
        File::delete($path);
    }
});

it('rejects a missing import archive', function () {
    $this->artisanCommand('content:import-public', ['path' => sys_get_temp_dir().'/missing-public-content.json'])
        ->expectsOutput('The public-content archive was not found.')
        ->assertFailed();
});

<?php

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

it('reports orphaned files while preserving referenced sources and variants', function () {
    $referencedPath = 'projects/project.webp';
    Storage::disk('public')->put($referencedPath, UploadedFile::fake()->image('project.webp', 800, 400)
        ->getContent());
    Storage::disk('public')->put('projects/responsive/project-640.webp', 'variant');
    Storage::disk('public')->put('orphan/unused.png', 'unused');

    Project::withoutEvents(fn () => Project::factory()
        ->published()
        ->create([
            'slug' => 'project',
            'featured_image_path' => $referencedPath,
        ]));

    $this->artisanCommand('media:find-orphans')
        ->expectsOutputToContain('Orphaned: orphan/unused.png')
        ->expectsOutputToContain('Found 1 orphaned files')
        ->expectsOutputToContain('No files were deleted')
        ->expectsOutputToContain('Media storage requires review.')
        ->assertFailed();

    Storage::disk('public')->assertExists([
        $referencedPath,
        'projects/responsive/project-640.webp',
        'orphan/unused.png',
    ]);
});

it('deletes only orphaned files when requested', function () {
    $referencedPath = 'projects/project.webp';
    Storage::disk('public')->put($referencedPath, 'referenced');
    Storage::disk('public')->put('projects/unused.png', 'unused');
    touch(Storage::disk('public')->path('projects/unused.png'), now()->subDays(2)
        ->getTimestamp());

    Project::withoutEvents(fn () => Project::factory()
        ->published()
        ->create([
            'slug' => 'project',
            'featured_image_path' => $referencedPath,
        ]));

    $this->artisanCommand('media:find-orphans', ['--delete' => true])
        ->expectsOutputToContain('Deleted: projects/unused.png')
        ->expectsOutputToContain('Deleted 1 orphaned files')
        ->assertSuccessful();

    Storage::disk('public')->assertExists($referencedPath);
    Storage::disk('public')->assertMissing('projects/unused.png');
});

it('preserves embedded attachments, unmanaged files, and recent uploads', function () {
    foreach (['projects/attachment.png', 'attachments/download.pdf', 'projects/recent.png'] as $path) {
        Storage::disk('public')->put($path, 'file');
    }
    touch(Storage::disk('public')->path('projects/attachment.png'), now()->subDays(2)
        ->getTimestamp());
    touch(Storage::disk('public')->path('attachments/download.pdf'), now()->subDays(2)
        ->getTimestamp());
    Project::withoutEvents(fn () => Project::factory()->create([
        'slug' => 'project',
        'content' => '![Screenshot](/storage/projects/attachment.png)',
    ]));

    $this->artisanCommand('media:find-orphans', ['--delete' => true])
        ->expectsOutputToContain('Deleted 0 orphaned files')
        ->assertFailed();

    Storage::disk('public')->assertExists(['projects/attachment.png', 'attachments/download.pdf', 'projects/recent.png']);
});

it('reports missing referenced files without treating them as orphans', function () {
    Project::withoutEvents(fn () => Project::factory()
        ->published()
        ->create([
            'slug' => 'project',
            'featured_image_path' => 'projects/missing.webp',
        ]));

    $this->artisanCommand('media:find-orphans')
        ->expectsOutputToContain('Found 0 orphaned files')
        ->expectsOutputToContain('Missing referenced files: 1.')
        ->expectsOutputToContain('Media storage requires review.')
        ->assertFailed();
});

it('succeeds when all stored files are referenced', function () {
    $path = 'projects/project.webp';
    Storage::disk('public')->put($path, 'referenced');

    Project::withoutEvents(fn () => Project::factory()
        ->published()
        ->create([
            'slug' => 'project',
            'featured_image_path' => $path,
        ]));

    $this->artisanCommand('media:find-orphans')
        ->expectsOutputToContain('Found 0 orphaned files')
        ->assertSuccessful();
});

it('keeps media referenced by trashed content when deleting orphans', function (Closure $createTrashedContent, string $path) {
    foreach ([$path, 'projects/unused.png'] as $file) {
        Storage::disk('public')->put($file, 'file');
        touch(Storage::disk('public')->path($file), now()->subDays(2)
            ->getTimestamp());
    }

    Model::withoutEvents(fn () => $createTrashedContent($path));

    $this->artisanCommand('media:find-orphans', ['--delete' => true])
        ->expectsOutputToContain('Deleted: projects/unused.png')
        ->doesntExpectOutputToContain($path)
        ->expectsOutputToContain('Found 1 orphaned files')
        ->expectsOutputToContain('Deleted 1 orphaned files')
        ->assertSuccessful();

    Storage::disk('public')->assertExists($path);
    Storage::disk('public')->assertMissing('projects/unused.png');
})->with([
    'project featured image' => [
        fn (string $path) => Project::factory()
            ->create(['slug' => 'project', 'featured_image_path' => $path])
            ->delete(),
        'projects/trashed.png',
    ],
    'post featured image' => [
        fn (string $path) => Post::factory()
            ->create(['slug' => 'post', 'category_id' => null, 'featured_image_path' => $path])
            ->delete(),
        'posts/trashed.png',
    ],
    'post embedded image' => [
        fn (string $path) => Post::factory()
            ->create(['slug' => 'post', 'category_id' => null, 'content' => "![Screenshot](/storage/{$path})"])
            ->delete(),
        'posts/embedded.png',
    ],
    'podcast cover image' => [
        fn (string $path) => Podcast::factory()
            ->create(['slug' => 'podcast', 'cover_image_path' => $path])
            ->delete(),
        'podcasts/trashed.png',
    ],
    'episode featured image' => [
        fn (string $path) => Episode::factory()
            ->create(['slug' => 'episode', 'podcast_id' => null, 'featured_image_path' => $path])
            ->delete(),
        'episodes/images/trashed.png',
    ],
    'newsletter issue embedded image' => [
        fn (string $path) => NewsletterIssue::factory()
            ->create(['slug' => 'issue', 'content' => "![Screenshot](/storage/{$path})"])
            ->delete(),
        'posts/newsletter.png',
    ],
]);

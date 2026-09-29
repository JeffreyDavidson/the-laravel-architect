<?php

use Carbon\CarbonInterface;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Support\PublishableFixtures;

pest()->use(RefreshDatabase::class);

/** Load the slug lock migration and run its up() step, as a deploy would. */
function runSlugLockMigration(): void
{
    $files = glob(database_path('migrations/*_add_slug_locked_at_to_content_tables.php'));
    $migration = $files === false || $files === [] ? null : require $files[0];

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new RuntimeException('The slug lock migration could not be loaded.');
    }

    $migration->up();
}

dataset('slug locking types', ['post', 'project', 'episode', 'newsletter issue']);

it('locks the slug of content that is already live', function (string $type) {
    $record = PublishableFixtures::ready($type);
    $record->publish();
    $record->forceFill(['slug_locked_at' => null])
        ->saveQuietly();

    runSlugLockMigration();
    $record->refresh();

    expect($record->getAttribute('slug_locked_at'))
        ->not->toBeNull();
})->with('slug locking types');

it('leaves draft slugs unlocked', function (string $type) {
    $record = PublishableFixtures::ready($type);

    runSlugLockMigration();
    $record->refresh();

    expect($record->getAttribute('slug_locked_at'))
        ->toBeNull();
})->with('slug locking types');

it('keeps an existing lock timestamp and can be run again', function () {
    $record = PublishableFixtures::ready('post');
    $record->publish();
    $record->forceFill(['slug_locked_at' => '2026-03-01 10:00:00'])
        ->saveQuietly();

    runSlugLockMigration();
    runSlugLockMigration();
    $record->refresh();
    $lockedAt = $record->getAttribute('slug_locked_at');

    expect(Schema::hasColumn('posts', 'slug_locked_at'))
        ->toBeTrue()
        ->and($lockedAt instanceof CarbonInterface ? $lockedAt->toDateTimeString() : null)
        ->toBe('2026-03-01 10:00:00');
});

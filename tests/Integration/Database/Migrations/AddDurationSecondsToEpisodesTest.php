<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

/** Load the duration migration and run its up() step, as a deploy would. */
function runDurationMigration(): void
{
    $files = glob(database_path('migrations/*_add_duration_seconds_to_episodes_table.php'));
    $migration = $files === false || $files === [] ? null : require $files[0];

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new RuntimeException('The duration migration could not be loaded.');
    }

    $migration->up();
}

function episodeDurationSeconds(string $slug): mixed
{
    return DB::table('episodes')
        ->where('slug', $slug)
        ->value('duration_seconds');
}

function insertEpisodeWithDuration(string $slug, ?int $minutes, ?int $seconds): void
{
    DB::table('episodes')->insert([
        'title' => $slug,
        'slug' => $slug,
        'description' => 'Description.',
        'duration_minutes' => $minutes,
        'duration_seconds' => $seconds,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('converts stored minutes to seconds exactly', function () {
    insertEpisodeWithDuration('twenty-five', 25, null);
    insertEpisodeWithDuration('long', 190, null);

    runDurationMigration();

    expect(episodeDurationSeconds('twenty-five'))
        ->toBe(1500)
        ->and(episodeDurationSeconds('long'))
        ->toBe(11400);
});

it('leaves episodes without a duration and existing seconds untouched', function () {
    insertEpisodeWithDuration('no-duration', null, null);
    insertEpisodeWithDuration('already-seconds', 5, 90);

    runDurationMigration();

    expect(episodeDurationSeconds('no-duration'))
        ->toBeNull()
        ->and(episodeDurationSeconds('already-seconds'))
        ->toBe(90);
});

it('can be run again without failing or changing converted values', function () {
    insertEpisodeWithDuration('repeat', 10, null);

    runDurationMigration();
    runDurationMigration();

    expect(Schema::hasColumn('episodes', 'duration_seconds'))
        ->toBeTrue()
        ->and(episodeDurationSeconds('repeat'))
        ->toBe(600);
});

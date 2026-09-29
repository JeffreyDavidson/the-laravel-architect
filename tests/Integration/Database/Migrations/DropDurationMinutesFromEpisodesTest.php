<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

/** Load the drop migration and run its up() step, as a deploy would. */
function runDropDurationMinutesMigration(): void
{
    $files = glob(database_path('migrations/*_drop_duration_minutes_from_episodes_table.php'));
    $migration = $files === false || $files === [] ? null : require $files[0];

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new RuntimeException('The drop migration could not be loaded.');
    }

    $migration->up();
}

/** The column is already gone after the migrations run, so put it back to simulate a database that still has it. */
function restoreDurationMinutesColumn(): void
{
    Schema::table('episodes', function (Blueprint $table) {
        $table->integer('duration_minutes')
            ->nullable();
    });
}

function insertEpisodeRow(string $slug, ?int $minutes, ?int $seconds): void
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

function storedEpisodeSeconds(string $slug): mixed
{
    return DB::table('episodes')
        ->where('slug', $slug)
        ->value('duration_seconds');
}

it('no longer has the retired minutes column once every migration has run', function () {
    expect(Schema::hasColumn('episodes', 'duration_minutes'))
        ->toBeFalse()
        ->and(Schema::hasColumn('episodes', 'duration_seconds'))
        ->toBeTrue();
});

it('drops the minutes column', function () {
    restoreDurationMinutesColumn();

    runDropDurationMinutesMigration();

    expect(Schema::hasColumn('episodes', 'duration_minutes'))
        ->toBeFalse();
});

it('converts any minutes the earlier backfill missed before dropping the column', function () {
    restoreDurationMinutesColumn();
    insertEpisodeRow('missed', 25, null);
    insertEpisodeRow('converted', 5, 90);
    insertEpisodeRow('empty', null, null);

    runDropDurationMinutesMigration();

    expect(storedEpisodeSeconds('missed'))
        ->toBe(1500)
        ->and(storedEpisodeSeconds('converted'))
        ->toBe(90)
        ->and(storedEpisodeSeconds('empty'))
        ->toBeNull();
});

it('can be run again after the column is gone', function () {
    restoreDurationMinutesColumn();
    insertEpisodeRow('kept', 10, null);

    runDropDurationMinutesMigration();
    runDropDurationMinutesMigration();

    expect(storedEpisodeSeconds('kept'))
        ->toBe(600);
});

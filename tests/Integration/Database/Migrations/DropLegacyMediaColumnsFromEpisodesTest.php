<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

/** Load the drop migration and run its up() step, as a deploy would. */
function runDropLegacyMediaMigration(): void
{
    $files = glob(database_path('migrations/*_drop_legacy_media_columns_from_episodes_table.php'));
    $migration = $files === false || $files === [] ? null : require $files[0];

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new RuntimeException('The drop migration could not be loaded.');
    }

    $migration->up();
}

/** The columns are already gone after the migrations run, so put them back to simulate a database that still has them. */
function restoreLegacyMediaColumns(): void
{
    Schema::table('episodes', function (Blueprint $table) {
        $table->string('audio_url')
            ->nullable();
        $table->string('audio_path')
            ->nullable();
        $table->string('embed_url')
            ->nullable();
    });
}

/** @param array<string, mixed> $legacy */
function insertEpisodeWithLegacyMedia(string $slug, array $legacy, bool $trashed = false): void
{
    DB::table('episodes')->insert([
        'title' => $slug,
        'slug' => $slug,
        'description' => 'Description.',
        'created_at' => now(),
        'updated_at' => now(),
        'deleted_at' => $trashed ? now() : null,
        ...$legacy,
    ]);
}

function episodeRowExists(string $slug): bool
{
    return DB::table('episodes')
        ->where('slug', $slug)
        ->exists();
}

function legacyMediaColumnsPresent(): bool
{
    return Schema::hasColumn('episodes', 'audio_url')
        || Schema::hasColumn('episodes', 'audio_path')
        || Schema::hasColumn('episodes', 'embed_url');
}

it('no longer has the retired media columns once every migration has run', function () {
    expect(legacyMediaColumnsPresent())
        ->toBeFalse();
});

it('drops the audio and embed columns when no episode holds legacy media', function () {
    restoreLegacyMediaColumns();
    insertEpisodeWithLegacyMedia('blank', ['audio_url' => '', 'audio_path' => null, 'embed_url' => '']);

    runDropLegacyMediaMigration();

    expect(legacyMediaColumnsPresent())
        ->toBeFalse()
        ->and(episodeRowExists('blank'))
        ->toBeTrue();
});

it('refuses to drop the columns while an episode still holds legacy media, and changes nothing', function (array $legacy, bool $trashed) {
    /** @var array<string, mixed> $legacy */
    restoreLegacyMediaColumns();
    insertEpisodeWithLegacyMedia('holds-media', $legacy, $trashed);

    expect(fn () => runDropLegacyMediaMigration())
        ->toThrow(RuntimeException::class, 'legacy media')
        ->and(Schema::hasColumn('episodes', 'audio_url'))
        ->toBeTrue()
        ->and(Schema::hasColumn('episodes', 'audio_path'))
        ->toBeTrue()
        ->and(Schema::hasColumn('episodes', 'embed_url'))
        ->toBeTrue()
        ->and(episodeRowExists('holds-media'))
        ->toBeTrue();
})->with([
    'hosted audio' => [['audio_url' => 'https://cdn.example.com/a.mp3'], false],
    'uploaded audio' => [['audio_path' => 'episodes/audio/a.mp3'], false],
    'a Spotify embed' => [['embed_url' => 'https://open.spotify.com/embed/episode/1'], false],
    'a trashed episode' => [['audio_path' => 'episodes/audio/a.mp3'], true],
]);

it('can be run again after the columns are gone', function () {
    restoreLegacyMediaColumns();

    runDropLegacyMediaMigration();
    runDropLegacyMediaMigration();

    expect(legacyMediaColumnsPresent())
        ->toBeFalse();
});

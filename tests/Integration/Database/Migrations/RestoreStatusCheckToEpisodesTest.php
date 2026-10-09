<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;

use function Pest\Laravel\artisan;

/*
 * The migration switches foreign keys off around its rebuild, which SQLite ignores inside a
 * transaction, so each test migrates its own in-memory database instead of using
 * RefreshDatabase, which wraps every test in a transaction.
 */
beforeEach(fn () => artisan('migrate'));

/** Load the restore migration and run its up() step, as a deploy would. */
function runRestoreEpisodesStatusCheckMigration(): void
{
    $files = glob(database_path('migrations/*_restore_status_check_to_episodes_table.php'));
    $migration = $files === false || $files === [] ? null : require $files[0];

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new RuntimeException('The restore migration could not be loaded.');
    }

    $migration->up();
}

/**
 * The CHECK is already restored after the migrations run, so rebuild episodes without it
 * to simulate a database that has not had the migration yet.
 */
function removeEpisodesStatusCheck(): void
{
    $indexes = episodesIndexSql();
    $unchecked = str_replace(
        ['CREATE TABLE "episodes"', ' check ("status" in (\'draft\', \'in_review\', \'published\', \'scheduled\'))'],
        ['CREATE TABLE "episodes_unchecked"', ''],
        episodesTableSql(),
    );

    Schema::withoutForeignKeyConstraints(function () use ($unchecked, $indexes): void {
        DB::statement($unchecked);
        DB::statement('insert into "episodes_unchecked" select * from "episodes"');
        DB::statement('drop table "episodes"');
        DB::statement('alter table "episodes_unchecked" rename to "episodes"');

        foreach ($indexes as $index) {
            DB::statement($index);
        }
    });
}

/**
 * Replace the (empty) episodes table with production's definition, which still has the original
 * scaffold's audio_file and featured_image columns and no status CHECK.
 */
function useProductionLegacyEpisodesTable(): void
{
    $indexes = episodesIndexSql();

    Schema::withoutForeignKeyConstraints(function () use ($indexes): void {
        DB::statement('drop table "episodes"');
        DB::statement('CREATE TABLE "episodes" ("id" integer primary key autoincrement not null, "title" varchar not null, "slug" varchar not null, "episode_number" integer, "season_number" integer not null default (\'1\'), "description" text not null, "show_notes" text, "audio_file" varchar, "youtube_url" varchar, "featured_image" varchar, "guest_name" varchar, "guest_title" varchar, "guest_url" varchar, "status" varchar not null default (\'draft\'), "published_at" datetime, "created_at" datetime, "updated_at" datetime, "podcast_id" integer, "featured_image_path" varchar, "transcript" text, "deleted_at" datetime, "transistor_url" varchar, "duration_seconds" integer, "slug_locked_at" datetime, foreign key("podcast_id") references "podcasts"("id") on delete cascade)');

        foreach ($indexes as $index) {
            DB::statement($index);
        }
    });
}

function episodesTableSql(): string
{
    $sql = DB::scalar("select sql from sqlite_master where type = 'table' and name = 'episodes'");

    if (! is_string($sql)) {
        throw new RuntimeException('The episodes table is missing.');
    }

    return $sql;
}

/**
 * @return list<string>
 */
function episodesIndexSql(): array
{
    $indexes = [];
    $rows = DB::table('sqlite_master')
        ->where('tbl_name', 'episodes')
        ->where('type', 'index')
        ->pluck('sql');

    foreach ($rows as $sql) {
        if (is_string($sql)) {
            $indexes[] = $sql;
        }
    }

    return $indexes;
}

/** @param array<string, mixed> $legacy */
function insertEpisodeWithStatus(string $slug, string $status, bool $trashed = false, array $legacy = []): int
{
    return DB::table('episodes')->insertGetId([
        'title' => $slug,
        'slug' => $slug,
        'description' => 'Description.',
        'status' => $status,
        'created_at' => now(),
        'updated_at' => now(),
        'deleted_at' => $trashed ? now() : null,
        ...$legacy,
    ]);
}

function linkEpisodeToNewPost(int $episodeId): void
{
    $postId = DB::table('posts')->insertGetId([
        'title' => "Post for episode {$episodeId}",
        'slug' => "post-for-episode-{$episodeId}",
        'content' => 'Post content.',
        'user_id' => User::factory()
            ->create()
            ->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('episode_post')->insert(['post_id' => $postId, 'episode_id' => $episodeId]);
}

it('restores the status check and keeps every episode, id and post link', function () {
    removeEpisodesStatusCheck();
    $live = insertEpisodeWithStatus('live', PublishStatus::Published->value);
    $trashed = insertEpisodeWithStatus('trashed', PublishStatus::Draft->value, trashed: true);
    linkEpisodeToNewPost($live);
    linkEpisodeToNewPost($trashed);

    runRestoreEpisodesStatusCheckMigration();
    $episodeIds = DB::table('episodes')
        ->pluck('id')
        ->all();
    $linkedEpisodeIds = DB::table('episode_post')
        ->pluck('episode_id')
        ->all();

    expect(episodesTableSql())
        ->toContain('check ("status" in (\'draft\', \'in_review\', \'published\', \'scheduled\'))')
        ->and($episodeIds)
        ->toBe([$live, $trashed])
        ->and($linkedEpisodeIds)
        ->toBe([$live, $trashed])
        ->and(DB::select('pragma foreign_key_check'))
        ->toBeEmpty()
        ->and(fn () => insertEpisodeWithStatus('unknown', 'unknown'))
        ->toThrow(QueryException::class);
});

it('refuses to rebuild while an episode has an unsupported status, and changes nothing', function (bool $trashed) {
    removeEpisodesStatusCheck();
    $before = episodesTableSql();
    insertEpisodeWithStatus('unsupported', 'archived', $trashed);

    expect(fn () => runRestoreEpisodesStatusCheckMigration())
        ->toThrow(RuntimeException::class, '1 episode(s) have a status outside PublishStatus')
        ->and(episodesTableSql())
        ->toBe($before)
        ->and(DB::table('episodes')->value('status'))
        ->toBe('archived');
})->with([
    'a live episode' => [false],
    'a trashed episode' => [true],
]);

it('rebuilds production\'s legacy table to the canonical definition, keeping every episode, id and post link', function () {
    $canonical = episodesTableSql();
    $indexes = episodesIndexSql();
    useProductionLegacyEpisodesTable();
    $live = insertEpisodeWithStatus('live', PublishStatus::Published->value, legacy: ['audio_file' => null, 'featured_image' => '']);
    $trashed = insertEpisodeWithStatus('trashed', PublishStatus::Draft->value, trashed: true);
    linkEpisodeToNewPost($live);
    linkEpisodeToNewPost($trashed);

    runRestoreEpisodesStatusCheckMigration();
    $episodeIds = DB::table('episodes')
        ->pluck('id')
        ->all();
    $linkedEpisodeIds = DB::table('episode_post')
        ->pluck('episode_id')
        ->all();

    expect(episodesTableSql())
        ->toBe($canonical)
        ->and(episodesIndexSql())
        ->toBe($indexes)
        ->and($episodeIds)
        ->toBe([$live, $trashed])
        ->and($linkedEpisodeIds)
        ->toBe([$live, $trashed])
        ->and(DB::select('pragma foreign_key_check'))
        ->toBeEmpty();
});

it('refuses to rebuild production\'s legacy table while a legacy column holds data, and changes nothing', function (array $legacy, bool $trashed) {
    /** @var array<string, mixed> $legacy */
    useProductionLegacyEpisodesTable();
    $before = episodesTableSql();
    insertEpisodeWithStatus('holds-legacy-data', PublishStatus::Draft->value, $trashed, $legacy);

    expect(fn () => runRestoreEpisodesStatusCheckMigration())
        ->toThrow(RuntimeException::class, '1 episode(s) still hold a legacy audio_file or featured_image value')
        ->and(episodesTableSql())
        ->toBe($before)
        ->and(DB::table('episodes')->count())
        ->toBe(1);
})->with([
    'an audio file' => [['audio_file' => 'episodes/audio/a.mp3'], false],
    'a featured image' => [['featured_image' => 'episodes/images/a.jpg'], false],
    'a trashed episode' => [['featured_image' => 'episodes/images/a.jpg'], true],
]);

it('refuses to rebuild a table that differs from the expected definition', function () {
    removeEpisodesStatusCheck();
    DB::statement('alter table "episodes" add column "unexpected" varchar');
    $before = episodesTableSql();

    expect(fn () => runRestoreEpisodesStatusCheckMigration())
        ->toThrow(RuntimeException::class, 'does not match the expected definition')
        ->and(episodesTableSql())
        ->toBe($before);
});

it('leaves the table alone once the status check is present', function () {
    $before = episodesTableSql();

    runRestoreEpisodesStatusCheckMigration();

    expect(episodesTableSql())
        ->toBe($before);
});

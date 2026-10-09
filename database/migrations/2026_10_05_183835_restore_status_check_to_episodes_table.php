<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;

return new class extends Migration
{
    /**
     * The episodes table as the earlier migrations leave it, without the status CHECK.
     */
    private const string UNCHECKED_TABLE = 'CREATE TABLE "episodes" ("id" integer primary key autoincrement not null, "title" varchar not null, "slug" varchar not null, "episode_number" integer, "season_number" integer not null default (\'1\'), "description" text not null, "show_notes" text, "youtube_url" varchar, "guest_name" varchar, "guest_title" varchar, "guest_url" varchar, "status" varchar not null default (\'draft\'), "published_at" datetime, "created_at" datetime, "updated_at" datetime, "podcast_id" integer, "featured_image_path" varchar, "transcript" text, "deleted_at" datetime, "transistor_url" varchar, "duration_seconds" integer, "slug_locked_at" datetime, foreign key("podcast_id") references "podcasts"("id") on delete cascade)';

    /**
     * The episodes table as production has it: the same, plus the original scaffold's
     * "audio_file" and "featured_image" columns. Commit 520732d8 removed them from the
     * create migration after production had already run it, so only production kept them.
     */
    private const string LEGACY_TABLE = 'CREATE TABLE "episodes" ("id" integer primary key autoincrement not null, "title" varchar not null, "slug" varchar not null, "episode_number" integer, "season_number" integer not null default (\'1\'), "description" text not null, "show_notes" text, "audio_file" varchar, "youtube_url" varchar, "featured_image" varchar, "guest_name" varchar, "guest_title" varchar, "guest_url" varchar, "status" varchar not null default (\'draft\'), "published_at" datetime, "created_at" datetime, "updated_at" datetime, "podcast_id" integer, "featured_image_path" varchar, "transcript" text, "deleted_at" datetime, "transistor_url" varchar, "duration_seconds" integer, "slug_locked_at" datetime, foreign key("podcast_id") references "podcasts"("id") on delete cascade)';

    /**
     * The table's indexes, in the order they were first created.
     *
     * @var list<string>
     */
    private const array INDEXES = [
        'CREATE UNIQUE INDEX "episodes_slug_unique" on "episodes" ("slug")',
        'CREATE INDEX "episodes_publication_listing_index" on "episodes" ("podcast_id", "status", "published_at", "id")',
    ];

    private const string COLUMNS = '"id", "title", "slug", "episode_number", "season_number", "description", "show_notes", "youtube_url", "guest_name", "guest_title", "guest_url", "status", "published_at", "created_at", "updated_at", "podcast_id", "featured_image_path", "transcript", "deleted_at", "transistor_url", "duration_seconds", "slug_locked_at"';

    /**
     * SQLite ignores PRAGMA foreign_keys inside a transaction, and dropping episodes while
     * foreign keys are enforced deletes every episode_post link through its ON DELETE CASCADE.
     * So this migration runs outside the migrator's transaction, switches enforcement off, and
     * wraps the rebuild in its own transaction.
     *
     * @var bool
     */
    public $withinTransaction = false;

    /**
     * Run the migrations.
     *
     * Adding podcast_id with the schema builder rebuilt episodes on SQLite and silently dropped
     * its status CHECK, so the database accepted any status and the PublishStatus cast then
     * failed on every page that loaded such an episode. SQLite cannot add a CHECK to an existing
     * table, so this rebuilds episodes by hand with the identical columns, foreign key and
     * indexes plus the CHECK, keeping every row, id and episode_post link. On production it also
     * drops the two legacy scaffold columns, so every database ends with the same definition.
     *
     * It does nothing once the CHECK is present. It stops before changing anything when the
     * table is neither known definition, a legacy column holds data, or an episode holds an
     * unsupported status.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite' || $this->hasStatusCheck()) {
            return;
        }

        $definition = $this->currentDefinition();
        $hasLegacyColumns = $definition === $this->sortedDefinition(self::LEGACY_TABLE);

        if (! $hasLegacyColumns && $definition !== $this->sortedDefinition(self::UNCHECKED_TABLE)) {
            throw new RuntimeException('The episodes table does not match the expected definition, so its status CHECK was not restored. Compare it with this migration before running it again.');
        }

        if ($hasLegacyColumns) {
            $holdingLegacyData = DB::table('episodes')
                ->where(fn ($query) => $query
                    ->whereNotNull('audio_file')
                    ->where('audio_file', '!=', ''))
                ->orWhere(fn ($query) => $query
                    ->whereNotNull('featured_image')
                    ->where('featured_image', '!=', ''))
                ->count();

            if ($holdingLegacyData > 0) {
                throw new RuntimeException("{$holdingLegacyData} episode(s) still hold a legacy audio_file or featured_image value, so the status CHECK was not restored. Move or clear those values, then run the migration again.");
            }
        }

        $statuses = array_column(PublishStatus::cases(), 'value');

        $unsupported = DB::table('episodes')
            ->whereNotIn('status', $statuses)
            ->count();

        if ($unsupported > 0) {
            throw new RuntimeException("{$unsupported} episode(s) have a status outside PublishStatus, so the status CHECK was not restored. Correct those statuses, then run the migration again.");
        }

        Schema::withoutForeignKeyConstraints(fn () => DB::transaction(fn () => $this->rebuild($statuses)));
    }

    private function hasStatusCheck(): bool
    {
        $sql = DB::scalar("select sql from sqlite_master where type = 'table' and name = 'episodes'");

        return is_string($sql) && stripos($sql, 'check ("status" in') !== false;
    }

    /**
     * The SQL of the episodes table and its indexes, sorted.
     *
     * @return list<string>
     */
    private function currentDefinition(): array
    {
        return DB::table('sqlite_master')
            ->where('tbl_name', 'episodes')
            ->whereNotNull('sql')
            ->orderBy('sql')
            ->pluck('sql')
            ->map(fn (mixed $sql): string => (string) $sql)
            ->all();
    }

    /**
     * The given table SQL with the expected indexes, sorted to compare with currentDefinition().
     *
     * @return list<string>
     */
    private function sortedDefinition(string $table): array
    {
        $definition = [$table, ...self::INDEXES];
        sort($definition);

        return $definition;
    }

    /**
     * Copy only the canonical columns into the canonical definition plus the CHECK, which
     * also leaves behind the legacy columns when production still has them.
     *
     * @param  list<string>  $statuses
     */
    private function rebuild(array $statuses): void
    {
        $allowed = implode(', ', array_map(fn (string $status): string => "'{$status}'", $statuses));
        $draft = PublishStatus::Draft->value;
        $columns = self::COLUMNS;
        $sequence = DB::scalar("select seq from sqlite_sequence where name = 'episodes'");

        DB::statement(str_replace(
            ['CREATE TABLE "episodes"', "\"status\" varchar not null default ('{$draft}')"],
            ['CREATE TABLE "episodes_new"', "\"status\" varchar check (\"status\" in ({$allowed})) not null default ('{$draft}')"],
            self::UNCHECKED_TABLE,
        ));
        DB::statement("insert into \"episodes_new\" ({$columns}) select {$columns} from \"episodes\"");
        DB::statement('drop table "episodes"');
        DB::statement('alter table "episodes_new" rename to "episodes"');

        foreach (self::INDEXES as $index) {
            DB::statement($index);
        }

        if ($sequence !== null) {
            DB::table('sqlite_sequence')->updateOrInsert(['name' => 'episodes'], ['seq' => $sequence]);
        }

        $violations = count(DB::select('pragma foreign_key_check("episodes")'))
            + count(DB::select('pragma foreign_key_check("episode_post")'));

        if ($violations > 0) {
            throw new RuntimeException("Rebuilding episodes left {$violations} foreign key violation(s), so the change was rolled back.");
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

/** Load the foreign key index migration and run its up() step, as a deploy would. */
function runForeignKeyIndexMigration(): void
{
    $files = glob(database_path('migrations/*_add_foreign_key_indexes_to_newsletter_deliveries_and_episode_post_tables.php'));
    $migration = $files === false || $files === [] ? null : require $files[0];

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new RuntimeException('The foreign key index migration could not be loaded.');
    }

    $migration->up();
}

dataset('foreign keys needing their own index', [
    'newsletter delivery subscriber' => ['newsletter_deliveries', 'subscriber_id', 'newsletter_deliveries_subscriber_id_index'],
    'episode post episode' => ['episode_post', 'episode_id', 'episode_post_episode_id_index'],
]);

it('looks up rows by the foreign key through its own index, as a cascading delete does', function (string $table, string $column, string $index) {
    $plan = collect(DB::select("explain query plan delete from \"{$table}\" where \"{$column}\" = 1"))
        ->pluck('detail')
        ->implode("\n");

    expect(Schema::hasIndex($table, [$column]))
        ->toBeTrue()
        ->and($plan)
        ->toContain("INDEX {$index} ({$column}=?)");
})->with('foreign keys needing their own index');

it('can be run again without duplicating the indexes', function (string $table, string $column) {
    runForeignKeyIndexMigration();
    $indexes = collect(Schema::getIndexes($table))
        ->where('columns', [$column]);

    expect($indexes)
        ->toHaveCount(1);
})->with('foreign keys needing their own index');

<?php

use App\Services\RestoredDatabaseComparison;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

pest()->use(RefreshDatabase::class);

it('reports live tables that are missing from the restored database', function () {
    $workDirectory = sys_get_temp_dir().'/tla-restored-database-test-'.bin2hex(random_bytes(6));
    File::ensureDirectoryExists($workDirectory);
    $dumpPath = "{$workDirectory}/database.sql";
    File::put($dumpPath, "CREATE TABLE migrations (id INTEGER PRIMARY KEY, migration TEXT, batch INTEGER);\n");

    try {
        $result = app(RestoredDatabaseComparison::class)
            ->compare($dumpPath, "{$workDirectory}/restored.sqlite");
    } finally {
        File::deleteDirectory($workDirectory);
    }

    expect($result->failures)
        ->toContain('Table categories is missing from the restored database.')
        ->not->toContain('Table migrations is missing from the restored database.')
        ->and($result->checks)
        ->toBe(['Restored and live databases passed PRAGMA quick_check.']);
});

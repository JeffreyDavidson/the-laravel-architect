<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\BackupComparisonResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;
use PDO;
use PDOException;

/**
 * Restores a backup's SQLite dump into an isolated file and compares it with
 * the live database: integrity, applied migrations, and persistent row counts.
 * Results never include process output or row contents.
 */
final class RestoredDatabaseComparison
{
    private const int RESTORE_TIMEOUT_SECONDS = 600;

    /** Tables whose rows churn constantly and are not meaningful restored state. */
    private const array TRANSIENT_TABLES = ['cache', 'cache_locks', 'sessions', 'jobs', 'job_batches'];

    public function compare(string $dumpPath, string $restoredPath): BackupComparisonResult
    {
        if (! $this->restoreDump($dumpPath, $restoredPath)) {
            return new BackupComparisonResult(failures: ['The database dump could not be restored.']);
        }

        $restored = new PDO("sqlite:{$restoredPath}");
        $restored->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return BackupComparisonResult::combine(
            $this->compareIntegrity($restored),
            $this->compareMigrations($restored),
            $this->compareRowCounts($restored),
        );
    }

    /**
     * Restore with the sqlite3 CLI that spatie/laravel-backup uses to create
     * the dump. Newer sqlite3 versions emit functions such as unistr() that
     * PHP's bundled SQLite cannot execute. Process output is never reported,
     * because it can quote backed-up rows.
     */
    private function restoreDump(string $dumpPath, string $restoredPath): bool
    {
        $dump = fopen($dumpPath, 'rb');

        if ($dump === false) {
            return false;
        }

        try {
            $result = Process::input($dump)
                ->timeout(self::RESTORE_TIMEOUT_SECONDS)
                ->run(['sqlite3', '-bail', $restoredPath]);
        } finally {
            fclose($dump);
        }

        return $result->successful();
    }

    private function compareIntegrity(PDO $restored): BackupComparisonResult
    {
        $restoredResult = $this->scalar($restored, 'PRAGMA quick_check');
        $liveResult = DB::scalar('PRAGMA quick_check');
        $failures = [];

        if ($restoredResult !== 'ok') {
            $failures[] = 'The restored database failed PRAGMA quick_check.';
        }

        if ($liveResult !== 'ok') {
            $failures[] = 'The live database failed PRAGMA quick_check.';
        }

        if ($failures !== []) {
            return new BackupComparisonResult(failures: $failures);
        }

        return new BackupComparisonResult(checks: ['Restored and live databases passed PRAGMA quick_check.']);
    }

    private function compareMigrations(PDO $restored): BackupComparisonResult
    {
        try {
            $statement = $restored->query('SELECT migration FROM migrations ORDER BY migration');
            $restoredMigrations = $statement === false
                ? null
                : $statement->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException) {
            $restoredMigrations = null;
        }

        $liveMigrations = DB::table('migrations')
            ->orderBy('migration')
            ->pluck('migration')
            ->all();

        if ($restoredMigrations !== $liveMigrations) {
            return new BackupComparisonResult(failures: ['The restored migrations do not match the live database.']);
        }

        $migrationCount = count($liveMigrations);

        return new BackupComparisonResult(checks: ["All {$migrationCount} migrations match the live database."]);
    }

    private function compareRowCounts(PDO $restored): BackupComparisonResult
    {
        $tables = $this->persistentTables();
        $failures = [];

        foreach ($tables as $table) {
            $quotedTable = '"'.str_replace('"', '""', $table).'"';

            try {
                $restoredCount = $this->scalar($restored, "SELECT COUNT(*) FROM {$quotedTable}");
            } catch (PDOException) {
                $failures[] = "Table {$table} is missing from the restored database.";

                continue;
            }

            $liveCount = DB::table($table)->count();

            $restoredRows = is_numeric($restoredCount) ? (int) $restoredCount : -1;

            if ($restoredRows !== $liveCount) {
                $failures[] = "Table {$table} has {$restoredRows} restored rows but {$liveCount} live rows.";
            }
        }

        if ($failures !== []) {
            return new BackupComparisonResult(failures: $failures);
        }

        $tableCount = count($tables);

        return new BackupComparisonResult(checks: ["Row counts match for {$tableCount} persistent tables."]);
    }

    private function scalar(PDO $database, string $sql): mixed
    {
        $statement = $database->query($sql);

        return $statement === false
            ? null
            : $statement->fetchColumn();
    }

    /** @return list<string> */
    private function persistentTables(): array
    {
        $tables = [];

        foreach (Schema::getTables() as $table) {
            $name = is_array($table) ? ($table['name'] ?? null) : null;

            if (is_string($name) && ! str_starts_with($name, 'sqlite_') && ! in_array($name, self::TRANSIENT_TABLES, true)) {
                $tables[] = $name;
            }
        }

        sort($tables);

        return $tables;
    }
}

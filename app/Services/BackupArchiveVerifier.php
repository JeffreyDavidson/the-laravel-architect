<?php

namespace App\Services;

use App\Data\BackupVerificationResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;
use PDO;
use PDOException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination;
use SplFileInfo;
use Throwable;
use ZipArchive;

/**
 * Independently verifies the newest backup archive on each destination by
 * restoring it into an isolated temporary directory and comparing it with the
 * live database and public media. Reports contain only counts, table names,
 * and pass/fail reasons, never credentials or backed-up content.
 */
final class BackupArchiveVerifier
{
    private const string DUMP_DIRECTORY = 'db-dumps/';

    private const string WORK_DIRECTORY_PREFIX = 'tla-backup-verify-';

    private const int SAMPLED_MEDIA_FILES = 5;

    private const int READ_CHUNK_BYTES = 1048576;

    private const int RESTORE_TIMEOUT_SECONDS = 600;

    /** Tables whose rows churn constantly and are not meaningful restored state. */
    private const array TRANSIENT_TABLES = ['cache', 'cache_locks', 'sessions', 'jobs', 'job_batches'];

    /** @var list<string> */
    private array $checks = [];

    /** @var list<string> */
    private array $failures = [];

    /** @return list<BackupVerificationResult> */
    public function verifyAll(): array
    {
        $results = [];

        foreach (config()->array('backup.backup.destination.disks') as $disk) {
            if (is_string($disk)) {
                $results[] = $this->verify($disk);
            }
        }

        return $results;
    }

    public function verify(string $disk): BackupVerificationResult
    {
        $this->checks = [];
        $this->failures = [];
        $password = config('backup.backup.password');

        if (! is_string($password) || $password === '') {
            return new BackupVerificationResult($disk, null, [], ['No backup archive password is configured.']);
        }

        $backup = BackupDestination::create($disk, config()->string('backup.backup.name'))
            ->newestBackup();

        if (! $backup instanceof Backup) {
            return new BackupVerificationResult($disk, null, [], ['No backup archive was found.']);
        }

        $archive = $backup->path();
        $workDirectory = $this->createWorkDirectory();

        try {
            $archivePath = "{$workDirectory}/archive.zip";
            $this->download($backup, $archivePath);
            $this->inspect($archivePath, $password, $workDirectory);
        } catch (Throwable $exception) {
            // Exception messages can echo SQL or file content, so only the type is reported.
            $exceptionClass = $exception::class;
            $this->failures[] = "Verification stopped unexpectedly ({$exceptionClass}).";
        } finally {
            $this->removeWorkDirectory($workDirectory);
        }

        $checks = $this->checks;
        $failures = $this->failures;

        return new BackupVerificationResult($disk, $archive, $checks, $failures);
    }

    private function inspect(string $archivePath, string $password, string $workDirectory): void
    {
        $zip = new ZipArchive;

        if ($zip->open($archivePath, ZipArchive::RDONLY) !== true) {
            $this->failures[] = 'The archive could not be opened.';

            return;
        }

        $zip->setPassword($password);
        $dumpPath = "{$workDirectory}/database.sql";

        try {
            $mediaHashes = $this->readEntries($zip, $dumpPath);
        } finally {
            $zip->close();
        }

        if ($this->failures !== []) {
            return;
        }

        if (! is_file($dumpPath)) {
            $this->failures[] = 'The archive contains no database dump.';

            return;
        }

        $this->verifyDatabase($dumpPath, "{$workDirectory}/restored.sqlite");
        $this->verifyMedia($mediaHashes);
    }

    /**
     * Read every entry in full, which proves each one decrypts and is intact.
     *
     * @return array<string, string> Media hashes keyed by their live path.
     */
    private function readEntries(ZipArchive $zip, string $dumpPath): array
    {
        $mediaRoots = $this->mediaRoots();
        $mediaHashes = [];
        $entryCount = $zip->count();

        for ($index = 0; $index < $entryCount; $index++) {
            $name = $zip->getNameIndex($index);
            $entry = $index + 1;

            if (! is_string($name) || ! $this->isSafeEntryName($name)) {
                $this->failures[] = "Entry {$entry} has an unsafe path.";

                continue;
            }

            $isDump = str_starts_with($name, self::DUMP_DIRECTORY);
            $livePath = $this->livePathFor($name, $mediaRoots);

            if (! $isDump && $livePath === null) {
                $this->failures[] = "Entry {$entry} is outside the database dump and media directories.";

                continue;
            }

            if (str_ends_with($name, '/')) {
                continue;
            }

            $hash = $this->readEntry($zip, $index, $isDump ? $dumpPath : null);

            if ($hash === null) {
                $this->failures[] = "Entry {$entry} could not be decrypted or read in full.";

                continue;
            }

            if ($livePath !== null) {
                $mediaHashes[$livePath] = $hash;
            }
        }

        if ($this->failures === []) {
            $this->checks[] = "Decrypted and read all {$entryCount} archive entries.";
        }

        return $mediaHashes;
    }

    /** Stream one encrypted entry, optionally copying it to disk, and return its SHA-256. */
    private function readEntry(ZipArchive $zip, int $index, ?string $copyTo): ?string
    {
        $stat = $zip->statIndex($index);

        if ($stat === false || $stat['encryption_method'] === ZipArchive::EM_NONE) {
            return null;
        }

        $stream = $zip->getStreamIndex($index);

        if ($stream === false) {
            return null;
        }

        $copy = $copyTo === null ? null : fopen($copyTo, 'xb');
        $context = hash_init('sha256');
        $bytes = 0;

        try {
            while (! feof($stream)) {
                $chunk = fread($stream, self::READ_CHUNK_BYTES);

                if ($chunk === false) {
                    return null;
                }

                $bytes += strlen($chunk);
                hash_update($context, $chunk);

                if (is_resource($copy)) {
                    fwrite($copy, $chunk);
                }
            }
        } finally {
            fclose($stream);

            if (is_resource($copy)) {
                fclose($copy);
            }
        }

        return $bytes === $stat['size'] ? hash_final($context) : null;
    }

    private function verifyDatabase(string $dumpPath, string $restoredPath): void
    {
        if (! $this->restoreDump($dumpPath, $restoredPath)) {
            $this->failures[] = 'The database dump could not be restored.';

            return;
        }

        $restored = new PDO("sqlite:{$restoredPath}");
        $restored->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->verifyIntegrity($restored);
        $this->verifyMigrations($restored);
        $this->verifyRowCounts($restored);
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

    private function verifyIntegrity(PDO $restored): void
    {
        $restoredResult = $this->scalar($restored, 'PRAGMA quick_check');
        $liveResult = DB::scalar('PRAGMA quick_check');

        if ($restoredResult !== 'ok') {
            $this->failures[] = 'The restored database failed PRAGMA quick_check.';
        }

        if ($liveResult !== 'ok') {
            $this->failures[] = 'The live database failed PRAGMA quick_check.';
        }

        if ($restoredResult === 'ok' && $liveResult === 'ok') {
            $this->checks[] = 'Restored and live databases passed PRAGMA quick_check.';
        }
    }

    private function verifyMigrations(PDO $restored): void
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
            $this->failures[] = 'The restored migrations do not match the live database.';

            return;
        }

        $migrationCount = count($liveMigrations);
        $this->checks[] = "All {$migrationCount} migrations match the live database.";
    }

    private function verifyRowCounts(PDO $restored): void
    {
        $tables = $this->persistentTables();
        $failuresBefore = count($this->failures);

        foreach ($tables as $table) {
            $quotedTable = '"'.str_replace('"', '""', $table).'"';

            try {
                $restoredCount = $this->scalar($restored, "SELECT COUNT(*) FROM {$quotedTable}");
            } catch (PDOException) {
                $this->failures[] = "Table {$table} is missing from the restored database.";

                continue;
            }

            $liveCount = DB::table($table)->count();

            $restoredRows = is_numeric($restoredCount) ? (int) $restoredCount : -1;

            if ($restoredRows !== $liveCount) {
                $this->failures[] = "Table {$table} has {$restoredRows} restored rows but {$liveCount} live rows.";
            }
        }

        if (count($this->failures) === $failuresBefore) {
            $tableCount = count($tables);
            $this->checks[] = "Row counts match for {$tableCount} persistent tables.";
        }
    }

    /** @param array<string, string> $mediaHashes */
    private function verifyMedia(array $mediaHashes): void
    {
        $liveFiles = $this->liveMediaFiles();
        $archivedCount = count($mediaHashes);
        $liveCount = count($liveFiles);

        if ($archivedCount !== $liveCount) {
            $this->failures[] = "The archive has {$archivedCount} media files but the live media directory has {$liveCount}.";

            return;
        }

        $sample = $archivedCount === 0
            ? []
            : (array) array_rand($mediaHashes, min(self::SAMPLED_MEDIA_FILES, $archivedCount));

        foreach ($sample as $livePath) {
            if (! is_string($livePath) || ! is_file($livePath) || hash_file('sha256', $livePath) !== $mediaHashes[$livePath]) {
                $this->failures[] = 'A sampled media file does not match its live copy.';

                return;
            }
        }

        $sampleCount = count($sample);
        $this->checks[] = "All {$archivedCount} media files are present; {$sampleCount} sampled SHA-256 hashes match.";
    }

    private function scalar(PDO $database, string $sql): mixed
    {
        $statement = $database->query($sql);

        return $statement === false
            ? null
            : $statement->fetchColumn();
    }

    private function isSafeEntryName(string $name): bool
    {
        if ($name === '' || str_starts_with($name, '/') || str_contains($name, '\\') || str_contains($name, "\0")) {
            return false;
        }

        return ! in_array('..', explode('/', $name), true);
    }

    /** @param list<string> $mediaRoots */
    private function livePathFor(string $name, array $mediaRoots): ?string
    {
        foreach ($mediaRoots as $root) {
            $prefix = ltrim($root, '/').'/';

            if (str_starts_with($name, $prefix)) {
                return "{$root}/".substr($name, strlen($prefix));
            }
        }

        return null;
    }

    /** @return list<string> */
    private function mediaRoots(): array
    {
        $roots = [];

        foreach (config()->array('backup.backup.source.files.include') as $root) {
            if (is_string($root) && $root !== '') {
                $roots[] = rtrim($root, '/');
            }
        }

        return $roots;
    }

    /** @return list<string> */
    private function liveMediaFiles(): array
    {
        $files = [];

        foreach ($this->mediaRoots() as $root) {
            if (! is_dir($root)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
            );

            foreach ($iterator as $file) {
                if (! $file instanceof SplFileInfo) {
                    continue;
                }

                $isRegularFile = $file->isFile();
                $isLink = $file->isLink();

                if ($isRegularFile && ! $isLink) {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
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

    private function download(Backup $backup, string $path): void
    {
        $source = $backup->stream();
        $target = fopen($path, 'xb');

        if (! is_resource($source) || $target === false) {
            throw new RuntimeException('The archive could not be downloaded.');
        }

        try {
            stream_copy_to_stream($source, $target);
        } finally {
            fclose($target);
            fclose($source);
        }
    }

    private function createWorkDirectory(): string
    {
        $directory = sys_get_temp_dir().'/'.self::WORK_DIRECTORY_PREFIX.bin2hex(random_bytes(8));

        if (! mkdir($directory, 0700) || ! chmod($directory, 0700)) {
            throw new RuntimeException('The isolated work directory could not be created.');
        }

        return $directory;
    }

    /** Remove only a directory this verifier created inside the system temp directory. */
    private function removeWorkDirectory(string $directory): void
    {
        $resolvedDirectory = realpath($directory);
        $resolvedTemp = realpath(sys_get_temp_dir());

        if ($resolvedDirectory === false || $resolvedTemp === false) {
            return;
        }

        if (! str_starts_with($resolvedDirectory, $resolvedTemp.DIRECTORY_SEPARATOR.self::WORK_DIRECTORY_PREFIX)) {
            return;
        }

        File::deleteDirectory($resolvedDirectory);
    }
}

<?php

namespace App\Services;

use App\Data\BackupComparisonResult;
use App\Data\BackupVerificationResult;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination;
use Throwable;
use ZipArchive;

/**
 * Independently verifies the newest backup archive on each destination by
 * restoring it into an isolated temporary directory and comparing it with the
 * live database and public media. Reports contain only counts, table names,
 * and pass/fail reasons, never credentials or backed-up content.
 *
 * This class owns downloading, decrypting, and reading the archive inside its
 * work directory. RestoredDatabaseComparison and BackupMediaComparison compare
 * the restored contents with the live application.
 */
final class BackupArchiveVerifier
{
    private const string DUMP_DIRECTORY = 'db-dumps/';

    private const string WORK_DIRECTORY_PREFIX = 'tla-backup-verify-';

    private const int READ_CHUNK_BYTES = 1048576;

    /** @var list<string> */
    private array $checks = [];

    /** @var list<string> */
    private array $failures = [];

    /** The directory that holds each run's isolated work directory. */
    private readonly string $workDirectoryParent;

    /**
     * The work directory parent defaults to the system temp directory. Tests
     * pass a private parent so they can prove cleanup of their own run without
     * racing other processes that verify backups in the shared temp directory.
     */
    public function __construct(
        ?string $workDirectoryParent = null,
        private readonly RestoredDatabaseComparison $databaseComparison = new RestoredDatabaseComparison,
        private readonly BackupMediaComparison $mediaComparison = new BackupMediaComparison,
    ) {
        $this->workDirectoryParent = $workDirectoryParent ?? sys_get_temp_dir();
    }

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

        $this->record($this->databaseComparison->compare($dumpPath, "{$workDirectory}/restored.sqlite"));
        $this->record($this->mediaComparison->compare($mediaHashes));
    }

    private function record(BackupComparisonResult $result): void
    {
        $this->checks = [...$this->checks, ...$result->checks];
        $this->failures = [...$this->failures, ...$result->failures];
    }

    /**
     * Read every entry in full, which proves each one decrypts and is intact.
     *
     * @return array<string, string> Media hashes keyed by their live path.
     */
    private function readEntries(ZipArchive $zip, string $dumpPath): array
    {
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
            $livePath = $this->mediaComparison->livePathFor($name);

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

    private function isSafeEntryName(string $name): bool
    {
        if ($name === '' || str_starts_with($name, '/') || str_contains($name, '\\') || str_contains($name, "\0")) {
            return false;
        }

        return ! in_array('..', explode('/', $name), true);
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
        $directory = "{$this->workDirectoryParent}/".self::WORK_DIRECTORY_PREFIX.bin2hex(random_bytes(8));

        if (! mkdir($directory, 0700) || ! chmod($directory, 0700)) {
            throw new RuntimeException('The isolated work directory could not be created.');
        }

        return $directory;
    }

    /** Remove only a directory this verifier created inside its work directory parent. */
    private function removeWorkDirectory(string $directory): void
    {
        $resolvedDirectory = realpath($directory);
        $resolvedParent = realpath($this->workDirectoryParent);

        if ($resolvedDirectory === false || $resolvedParent === false) {
            return;
        }

        if (! str_starts_with($resolvedDirectory, $resolvedParent.DIRECTORY_SEPARATOR.self::WORK_DIRECTORY_PREFIX)) {
            return;
        }

        File::deleteDirectory($resolvedDirectory);
    }
}

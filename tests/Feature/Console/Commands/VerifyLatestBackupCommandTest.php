<?php

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

const BACKUP_PASSWORD = 'archive-password';

beforeEach(function () {
    $mediaRoot = sys_get_temp_dir().'/tla-media-'.bin2hex(random_bytes(6));
    File::ensureDirectoryExists("{$mediaRoot}/posts");
    File::put("{$mediaRoot}/posts/cover.jpg", 'cover image bytes');
    File::put("{$mediaRoot}/posts/cover-640.webp", 'variant bytes');
    Storage::fake('backups');
    config()->set([
        'backup.backup.name' => 'tla',
        'backup.backup.password' => BACKUP_PASSWORD,
        'backup.backup.destination.disks' => ['backups'],
        'backup.backup.source.files.include' => [$mediaRoot],
    ]);
});

afterEach(function () {
    File::deleteDirectory(testMediaRoot());
});

/** The media root configured for this test's backups. */
function testMediaRoot(): string
{
    $root = config()->array('backup.backup.source.files.include')[0] ?? null;

    if (! is_string($root)) {
        throw new RuntimeException('A media root must be configured for backup verification tests.');
    }

    return $root;
}

/** @return list<string> */
function verificationWorkDirectories(): array
{
    return glob(sys_get_temp_dir().'/tla-backup-verify-*') ?: [];
}

/** Write the live test database as SQL, like sqlite3's .dump. */
function liveDatabaseDump(): string
{
    $pdo = DB::connection()
        ->getPdo();
    $sql = "PRAGMA foreign_keys=OFF;\nBEGIN TRANSACTION;\n";
    $tables = DB::select("SELECT name, sql FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'");

    foreach ($tables as $table) {
        $name = data_get($table, 'name');
        $createStatement = data_get($table, 'sql');

        if (! is_string($name) || ! is_string($createStatement)) {
            continue;
        }

        $sql .= "{$createStatement};\n";
        $rows = DB::table($name)
            ->get();

        foreach ($rows as $row) {
            $values = array_map(
                fn (mixed $value): string => is_scalar($value) ? $pdo->quote((string) $value) : 'NULL',
                (array) $row,
            );
            $sql .= "INSERT INTO \"{$name}\" VALUES(".implode(',', $values).");\n";
        }
    }

    return "{$sql}COMMIT;\n";
}

/**
 * Build an archive in spatie/laravel-backup's layout: the dump under db-dumps/
 * and media under its absolute path without the leading slash.
 *
 * @param  array<string, string>  $extraEntries
 */
function storeBackupArchive(?string $dump = null, array $extraEntries = [], string $password = BACKUP_PASSWORD, bool $encrypt = true): void
{
    $path = Storage::disk('backups')
        ->path('tla/2026-09-26-12-00-00.zip');
    File::ensureDirectoryExists(dirname($path));
    $zip = new ZipArchive;
    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $entries = ['db-dumps/sqlite-database.sql' => $dump ?? liveDatabaseDump(), ...$extraEntries];

    foreach (File::allFiles(testMediaRoot()) as $file) {
        $contents = $file->getContents();
        $entries[ltrim($file->getPathname(), '/')] = $contents;
    }

    foreach ($entries as $name => $contents) {
        $zip->addFromString($name, $contents);

        if ($encrypt) {
            $zip->setEncryptionName($name, ZipArchive::EM_AES_256, $password);
        }
    }

    $zip->close();
}

it('verifies a faithful encrypted archive', function () {
    $workDirectoriesBefore = verificationWorkDirectories();
    Category::query()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    storeBackupArchive();

    $this->artisanCommand('app:verify-backup')
        ->expectsOutputToContain('Decrypted and read all 3 archive entries.')
        ->expectsOutputToContain('Restored and live databases passed PRAGMA quick_check.')
        ->expectsOutputToContain('migrations match the live database.')
        ->expectsOutputToContain('persistent tables.')
        ->expectsOutputToContain('All 2 media files are present; 2 sampled SHA-256 hashes match.')
        ->expectsOutputToContain('The backup is verified and restorable.')
        ->assertSuccessful();

    expect(verificationWorkDirectories())
        ->toBe($workDirectoriesBefore);
});

it('fails without a usable archive or password', function (Closure $arrange, string $failure) {
    $arrange();

    $this->artisanCommand('app:verify-backup')
        ->expectsOutputToContain($failure)
        ->assertFailed();
})->with([
    'no archive' => [
        fn () => null,
        'No backup archive was found.',
    ],
    'no password configured' => [
        function (): void {
            storeBackupArchive();
            config()->set('backup.backup.password', null);
        },
        'No backup archive password is configured.',
    ],
]);

it('rejects archives that cannot be trusted', function (Closure $arrange, string $failure) {
    $workDirectoriesBefore = verificationWorkDirectories();
    $arrange();

    $this->artisanCommand('app:verify-backup')
        ->expectsOutputToContain($failure)
        ->expectsOutputToContain('The backup could not be verified.')
        ->assertFailed();

    expect(verificationWorkDirectories())
        ->toBe($workDirectoriesBefore);
})->with([
    'wrong password' => [
        fn () => storeBackupArchive(password: 'a-different-password'),
        'could not be decrypted or read in full.',
    ],
    'unencrypted entries' => [
        fn () => storeBackupArchive(encrypt: false),
        'could not be decrypted or read in full.',
    ],
    'path escaping the media directory' => [
        fn () => storeBackupArchive(extraEntries: ['db-dumps/../../etc/passwd' => 'x']),
        'has an unsafe path.',
    ],
    'file outside the backed-up directories' => [
        fn () => storeBackupArchive(extraEntries: ['home/forge/.ssh/id_rsa' => 'x']),
        'is outside the database dump and media directories.',
    ],
    'corrupt database dump' => [
        fn () => storeBackupArchive(dump: 'CREATE TABLE broken ('),
        'The database dump could not be restored.',
    ],
    'migration missing from the backup' => [
        function (): void {
            storeBackupArchive();
            DB::table('migrations')->insert(['migration' => '2026_09_27_000000_newer_migration', 'batch' => 99]);
        },
        'The restored migrations do not match the live database.',
    ],
    'rows written after the backup' => [
        function (): void {
            storeBackupArchive();
            Category::query()->create(['name' => 'Architecture', 'slug' => 'architecture']);
        },
        'Table categories has 0 restored rows but 1 live rows.',
    ],
    'media added after the backup' => [
        function (): void {
            storeBackupArchive();
            File::put(testMediaRoot().'/posts/new.jpg', 'new image');
        },
        'The archive has 2 media files but the live media directory has 3.',
    ],
    'media changed after the backup' => [
        function (): void {
            storeBackupArchive();
            File::put(testMediaRoot().'/posts/cover.jpg', 'replaced image bytes');
            File::put(testMediaRoot().'/posts/cover-640.webp', 'replaced variant bytes');
        },
        'A sampled media file does not match its live copy.',
    ],
]);

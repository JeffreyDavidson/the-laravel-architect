# Backups

Production takes an encrypted backup every day, verifies it straight away, and
stores it off the server on Backblaze B2. Staging takes no scheduled backups
(see [Scheduled tasks](scheduled-tasks.md)). To restore one, follow
[Restore and rollback](restore-and-rollback.md).

Application archives contain only the SQLite database dump and the uploaded
media directory (`BACKUP_MEDIA_PATH`). Source code is recovered from GitHub, and
`.env` must remain excluded.

## Off-server destination

Production uses `b2-backups` as its sole scheduled backup destination, confirmed
and retained by operator decision on 2026-09-19. Backup creation and monitoring
must cover that same destination. Local and NAS copies are not required by the
current policy; adding another destination requires a separate operational
decision. This leaves B2 as the only off-server backup destination, so preserve
archive encryption, failure notifications, and regular restore validation.

Backblaze B2 provides an encrypted off-server copy through its S3-compatible
API. Configure these values through the production secret manager, never in the
repository:

```dotenv
BACKUP_DISKS=b2-backups
BACKUP_B2_KEY_ID=<bucket-scoped key ID>
BACKUP_B2_APPLICATION_KEY=<bucket-scoped application key>
BACKUP_B2_REGION=us-east-005
BACKUP_B2_BUCKET=jdavidson-tla-production-backups
BACKUP_B2_ENDPOINT=https://s3.us-east-005.backblazeb2.com
BACKUP_ARCHIVE_PASSWORD=<independent archive password>
```

Restrict the B2 application key to the TLA backup bucket with read and write
access. Keep the bucket private, retain client-side archive encryption, and do
not reuse the Mouse28 key or bucket. The configured endpoint must use HTTPS on
Backblaze's `backblazeb2.com` domain.

After an approved change to these values, refresh the configuration and prove
the B2 destination works:

```bash
php artisan config:clear
php artisan app:verify-production
php artisan backup:run
php artisan backup:monitor
php artisan app:verify-backup
```

Confirm a new encrypted archive exists on the `b2-backups` disk and that
`app:verify-backup` passes against the copy it downloads from B2. A successful
connection does not prove that an application backup can be restored.

## Backup monitoring

Backup monitoring uses the same configured disks as backup creation, with a
maximum age of one day and a 5,000 MB storage limit in `config/backup.php`. It
runs daily at 04:00 on production and emails failures. This is periodic
detection, not continuous monitoring.

The obsolete `BACKUP_MAX_AGE_HOURS` setting is no longer read; remove it during
an approved environment maintenance change if present. Validated pre-migration
backups and restore drills remain required independently of the release
verifier.

Run `php artisan app:test-backup-notification` after configuring or changing the
production mail transport. The command sends an identifiable test message to
`BACKUP_NOTIFICATION_EMAIL` and does not create a backup.

## Automated archive verification

`php artisan app:verify-backup` performs these independent checks against the
newest archive on every configured destination:

- It downloads the archive into a new `0700` directory under the system temp
  directory.
- Every file entry must be encrypted, decrypt, and read in full at its recorded
  size.
- Every path must be a database dump or sit under `BACKUP_MEDIA_PATH`.
- It restores the dump with the same `sqlite3` CLI that creates it, runs
  `PRAGMA quick_check` on the restored and live databases, and compares the
  migration list and every persistent table's row count. `cache`, `cache_locks`,
  `sessions`, `jobs`, and `job_batches` are excluded as transient.
- It compares the media file count and five sampled SHA-256 hashes with the live
  media directory.

The temporary directory is always removed, and the output contains only counts,
table names, and pass or fail reasons, never the archive password or backed-up
content.

Run it straight after `backup:run`: a write between the two commands shows up as
a row-count or media mismatch, so rerun both.

On production, the scheduler runs it every day straight after the scheduled
backup. Both are due at `BACKUP_RUN_AT` (default `02:00`), and one scheduler run
executes due tasks one at a time in the order `routes/console.php` defines them,
so verification starts the moment the backup finishes. Keep `app:verify-backup`
defined after `backup:run`, and never run the backup in the background, or the
two would race. A failure is emailed to the backup notification address like the
other scheduled checks; if the backup itself failed, verification checks the
previous archive and usually fails too.

A verified backup therefore normally exists at deploy time, and the production
deploy script also takes and verifies a fresh one before it applies any pending
migration (see [Forge deploy script](forge-deploy-script.md#backup-gate)), so no
manual step is needed for a release with migrations. A non-zero exit means the
backup must not be relied on for a release. The manual drill below remains the
fallback check; an actual restore follows
[Restore from backup](restore-and-rollback.md#restore-from-backup).

The old `BACKUP_VERIFY_AT` setting is no longer read; remove it from the
production environment during an approved maintenance change if present.

## Manual checks

An exit-zero backup command is not enough. Independently verify:

- SQLite `PRAGMA quick_check` returns `ok` for the live database and snapshot.
- Source and snapshot migration counts match.
- Source and snapshot record counts match for affected tables.
- PHP's `ZipArchive` can decrypt and read every archive entry.
- The archive contains only files from the intended media root.
- Archived file count matches the source file count.

Retain the artifacts until the release is independently verified.

## Encrypted restore drill

Perform this drill in an isolated temporary directory, never over the live
database or media directory:

1. Set a restrictive umask and create a unique directory with `mktemp -d`.
2. Copy one explicit backup archive into that directory. Confirm its resolved
   source path before copying.
3. Supply `BACKUP_ARCHIVE_PASSWORD` through the process environment or approved
   secret manager. Never paste it into a command, log, ticket, or shell history.
4. Use PHP's `ZipArchive` to set the password, test every encrypted entry, and
   extract the archive into a child directory. Stop if any entry cannot be
   decrypted or read.
5. Rebuild a database from the extracted SQL dump (`db-dumps/sqlite-*.sql`) with
   `sqlite3 -bail restored.sqlite < dump.sql`, then run `PRAGMA quick_check` on
   it; require exactly `ok`.
6. Compare the restored and live migration lists and the record counts for
   critical tables.
7. Confirm restored media paths remain inside the isolated extraction root, then
   compare file counts and sample file hashes.
8. Record the archive timestamp, checks performed, and result without recording
   credentials or private content.
9. After review, verify the temporary path again and remove only that isolated
   restore directory.

### Latest completed drill

2026-09-23: archive `2026-09-23-02-00-11.zip` (02:00:17 UTC).

- All 25 ZIP entries decrypted and read.
- The live and restored SQLite databases passed `PRAGMA quick_check`; all 36
  migrations and row counts for 20 persistent tables matched. `cache_locks` and
  `sessions` counts changed between the backup and live database, as expected
  for transient tables.
- All 24 public-media paths and the total file count matched, and five sampled
  SHA-256 hashes matched.
- The isolated temporary copy was removed.

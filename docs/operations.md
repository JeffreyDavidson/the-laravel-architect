# Production operations

This runbook is for deployments to the Laravel Forge production server.

## Before deploying

1. Confirm the target commit and review its migration and storage changes.
2. Confirm `DB_DATABASE` points to the persistent live SQLite database, not a release-local copy.
3. Confirm `BACKUP_MEDIA_PATH` points to the persistent `storage/app/public` directory.
4. Create and independently validate a SQLite snapshot and public-media archive.
5. Keep both artifacts until the deployment and post-deployment checks are complete.

For a migration that changes media or database structure, do not proceed without a valid database snapshot and a valid media archive.

## Deploying

The Forge deployment should install locked Composer dependencies, build assets, run forward-only migrations, refresh optimized caches, and restart the queue worker. The scheduler must continue running every minute.

Run `php artisan app:verify-production` after loading the release environment and before applying migrations. Stop the deployment if the command reports an unsafe or incomplete setting.

After enabling runtime monitoring or clearing the application cache, run `php artisan schedule:run` and allow the queue worker to process the heartbeat probe before relying on `/up`.

Do not run a standalone production migration unless the deployment itself cannot apply the migration and the release plan explicitly authorizes it.

## After deploying

Run the deployment verifier with the immutable commit expected for the release:

```bash
php artisan app:verify-deployment EXPECTED_COMMIT_SHA
```

The command fails when the checked-out commit differs, migrations are pending, queue or scheduler heartbeats are stale, or any configured backup disk lacks a fresh backup. Then verify all of the following against the deployed commit:

- Production `HEAD` matches the expected commit.
- `php artisan migrate:status` has no pending migrations.
- The migration was recorded exactly once with a positive batch.
- The expected schema is present and obsolete schema is absent.
- `/` and `/admin` return the expected status codes.
- `/up` returns HTTP 200, confirming the application can read its migrated database and both the scheduler and queue worker have fresh heartbeats.
- Public media URLs return successful responses.
- The queue worker and scheduler are active.
- A reversible upload smoke test can create, read, and delete a temporary object.
- The manually dispatched `Production smoke` GitHub Actions workflow passes. It is also run every six hours.

For content or authorization changes, also verify the affected public route and authenticated admin boundary.

## Backup validation

An exit-zero backup command is not enough. Independently verify:

- SQLite `PRAGMA quick_check` returns `ok` for the live database and snapshot.
- Source and snapshot migration counts match.
- Source and snapshot record counts match for affected tables.
- `gzip -t` passes for media archives.
- The archive contains only files from the intended media root.
- Archived file count matches the source file count.

Retain the artifacts until the release is independently verified. Copy them to an approved off-server destination as soon as the security and retention policy allows.

### Encrypted restore drill

Perform this drill in an isolated temporary directory, never over the live database or media directory:

1. Set a restrictive umask and create a unique directory with `mktemp -d`.
2. Copy one explicit backup archive into that directory. Confirm its resolved source path before copying.
3. Supply `BACKUP_ARCHIVE_PASSWORD` through the process environment or approved secret manager. Never paste it into a command, log, ticket, or shell history.
4. Use PHP's `ZipArchive` to set the password, test every encrypted entry, and extract the archive into a child directory. Stop if any entry cannot be decrypted or read.
5. Locate the extracted SQLite database and run `PRAGMA quick_check`; require exactly `ok`.
6. Compare the restored and live migration lists and the record counts for critical tables.
7. Confirm restored media paths remain inside the isolated extraction root, then compare file counts and sample file hashes.
8. Record the archive timestamp, checks performed, and result without recording credentials or private content.
9. After review, verify the temporary path again and remove only that isolated restore directory.

Run `php artisan app:test-backup-notification` after configuring or changing the production mail transport. The command sends an identifiable test message to `BACKUP_NOTIFICATION_EMAIL` and does not create a backup.

Failed jobs are checked hourly against `QUEUE_FAILED_JOB_ALERT_THRESHOLD`. Failures are retained for `QUEUE_FAILED_JOB_RETENTION_HOURS` and then pruned by Laravel's native `queue:prune-failed` command.

## Rollback

1. Stop the release if post-deployment verification fails.
2. Do not restore over the live database or media directory until the exact targets are confirmed.
3. Restore the validated SQLite snapshot and media archive using the approved Forge procedure.
4. Redeploy the last known-good commit.
5. Re-run migration, route, media, queue, and scheduler verification.
6. Record the failure, restoration commands, artifact paths, and final production commit.

Never delete the only validated rollback artifacts during an incident.

## Forge API migration

The legacy Forge API v1 is deprecated and is scheduled to be discontinued on August 31, 2026. Follow [`docs/forge-api-migration.md`](forge-api-migration.md) before that date. Application deployment must not depend on undocumented v1 requests.

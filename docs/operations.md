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

Do not run a standalone production migration unless the deployment itself cannot apply the migration and the release plan explicitly authorizes it.

## After deploying

Verify all of the following against the deployed commit:

- Production `HEAD` matches the expected commit.
- `php artisan migrate:status` has no pending migrations.
- The migration was recorded exactly once with a positive batch.
- The expected schema is present and obsolete schema is absent.
- `/` and `/admin` return the expected status codes.
- `/up` returns HTTP 200, confirming the application can read its migrated database.
- Public media URLs return successful responses.
- The queue worker and scheduler are active.
- A reversible upload smoke test can create, read, and delete a temporary object.

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

## Rollback

1. Stop the release if post-deployment verification fails.
2. Do not restore over the live database or media directory until the exact targets are confirmed.
3. Restore the validated SQLite snapshot and media archive using the approved Forge procedure.
4. Redeploy the last known-good commit.
5. Re-run migration, route, media, queue, and scheduler verification.
6. Record the failure, restoration commands, artifact paths, and final production commit.

Never delete the only validated rollback artifacts during an incident.

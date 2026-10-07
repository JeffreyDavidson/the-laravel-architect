# Restore and rollback

How to put the site back after a bad release or data loss. Backups and their
verification are covered in [Backups](backups.md).

## Restore from backup

Restoring replaces the live database and media with an older copy and loses
every change made since that backup. It runs only with the owner's explicit
approval for the specific archive, and only the owner runs it on the server:
agents never run any part of it. Practise it on staging, never first on
production. Run the commands as the `forge` user from the site's `current`
directory (`/home/forge/thelaravelarchitect.com/current` on production), and
never paste the archive password anywhere.

1. Choose the archive with the owner and record its name (`php artisan backup:list` shows them; on B2 they sit under `<APP_NAME>/`). Run `php artisan app:verify-backup` first if it is the newest one.
2. Note the live database path (`php artisan tinker --execute 'echo config("database.connections.sqlite.database");'`) and the media path (`BACKUP_MEDIA_PATH`).
3. Put the site in maintenance mode with `php artisan down`. In Forge, stop the production queue worker and pause the production scheduler, so nothing writes while the files change. Maintenance mode also holds back the scheduler and worker, but stopping them in Forge keeps them from holding the old database open.
4. Download and decrypt the archive into a private temporary directory. The archive is an AES-encrypted ZIP; the password is read from the application's configuration, so it never reaches the shell:

   ```bash
   umask 077
   export TLA_RESTORE_DIR="$(mktemp -d)"
   export TLA_ARCHIVE="<APP_NAME>/<archive>.zip"
   php artisan tinker --execute 'file_put_contents(getenv("TLA_RESTORE_DIR")."/backup.zip", Storage::disk("b2-backups")->readStream(getenv("TLA_ARCHIVE")));'
   php artisan tinker --execute '$zip = new ZipArchive; $zip->open(getenv("TLA_RESTORE_DIR")."/backup.zip"); $zip->setPassword(config("backup.backup.password")); echo $zip->extractTo(getenv("TLA_RESTORE_DIR")."/extracted") ? "extracted" : "FAILED";'
   ```

   Stop unless it prints `extracted`.
5. The archive holds a plain SQL dump (made with `sqlite3 .dump`), not a copy of the database file. Rebuild a new database file from it and check it:

   ```bash
   sqlite3 -bail "$TLA_RESTORE_DIR/restored.sqlite" < "$TLA_RESTORE_DIR"/extracted/db-dumps/sqlite-*.sql
   sqlite3 "$TLA_RESTORE_DIR/restored.sqlite" 'PRAGMA quick_check;'
   ```

   Require exactly `ok`. The application switches the file to WAL mode when it next connects.
6. Stop PHP access to the database: stop PHP-FPM (`sudo service php8.5-fpm stop`, using the server's PHP version). Staging shares this PHP-FPM, so it is down for the same few minutes.
7. Swap the file in. Move the live database aside together with its `-wal` and `-shm` files, never deleting them, so no stale WAL file is applied to the restored database, then copy the restored file into place and match the old file's owner and permissions (`ls -l`):

   ```bash
   TLA_DB="<live database path>"
   TLA_STAMP="$(date -u +%Y%m%d%H%M%S)"
   for tla_suffix in "" -wal -shm; do
       if test -e "$TLA_DB$tla_suffix"; then mv "$TLA_DB$tla_suffix" "$TLA_DB$tla_suffix.before-restore-$TLA_STAMP"; fi
   done
   cp "$TLA_RESTORE_DIR/restored.sqlite" "$TLA_DB"
   ```

8. Restore public media the same way: move the live `BACKUP_MEDIA_PATH` directory aside with the same suffix, then copy the extracted copy into place. The archive stores media under its full path, so it is at `$TLA_RESTORE_DIR/extracted` followed by `BACKUP_MEDIA_PATH`.
9. Start PHP-FPM again (`sudo service php8.5-fpm start`), then clear the caches, including the database cache table restored with the dump, and rebuild them: `php artisan optimize:clear` then `php artisan optimize`.
10. While still in maintenance mode, run `php artisan migrate:status`. If the archive predates a migration in the active release, a migration shows as pending: stop and decide with the owner whether to redeploy the release that matches the archive (see [Rollback](#rollback)) or migrate. Check the restored `jobs` table as well: jobs queued at backup time may already have run since (a contact email, a newsletter delivery) and would run again.
11. Restart the queue worker and resume the scheduler in Forge, then leave maintenance mode with `php artisan up`. The scheduler and worker do not run while the site is down, so the runtime checks below only pass after this step.
12. Verify: `/up` returns HTTP 200, `/deployment.json` still shows the active revision, `php artisan app:verify-deployment <that revision>` passes, and `/`, `/blog`, a post, a public media URL and the `/admin` sign-in load. Run `php artisan media:verify-responsive-images`.
13. Record the archive, the time of the restore and the checks in the incident notes, without credentials or private content. Keep the `.before-restore-*` copies until the owner agrees they can go, then remove only the temporary restore directory.

## Rollback

1. Stop the release if post-deployment verification fails.
2. Do not restore over the live database or media directory until the exact targets are confirmed.
3. With the owner's explicit approval, restore the validated SQLite snapshot and media archive by following [Restore from backup](#restore-from-backup).
4. Redeploy the last known-good commit.
5. Re-run migration, route, media, queue, and scheduler verification.
6. Record the failure, restoration commands, artifact paths, and final production commit.

Never delete the only validated rollback artifacts during an incident.

Code rollback does not undo database migrations, and rollback is an explicit
operational decision, not automatic branch rewriting. See
[Hotfixes and rollback](../releases.md#hotfixes-and-rollback) in the release
process.

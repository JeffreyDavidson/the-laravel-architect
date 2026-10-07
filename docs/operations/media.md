# Media

Commands for uploaded media on the `public` disk: setting up storage, repairing
and checking responsive image variants, generating post artwork, and reviewing
orphaned files. How media is stored and cleaned up is described in
[Media architecture](../architecture/media.md).

## New environments

Run `php artisan storage:link` on a new environment. The
[Forge deploy script](forge-deploy-script.md) recreates the public storage link
in every release.

## Responsive image repair

Use `php artisan media:repair-responsive-images` when backfilling or repairing
existing uploads. When a release introduces responsive uploaded images, run it
once after the persistent public-media directory is mounted.

- The command repairs projects, posts, and podcasts in one bounded, isolated
  run while preserving original uploads, creating WebP derivatives beside them,
  and skipping derivatives that already pass verification.
- It returns a failure if any source file is missing, unsupported, or still
  unhealthy after the aggregate verification pass.
- Concurrent repair or resource-specific generation runs are rejected so they
  cannot race over the same derivatives.
- Use `--force` only when a release intentionally requires every valid
  derivative to be re-encoded.
- The resource-specific generation commands remain available for targeted
  recovery.
- Do not remove the original images.

The admin panel's Media Health page also offers a confirmed **Repair variants**
action where repair is possible (see
[Admin panel](../architecture/admin-panel.md#custom-pages)).

## Responsive image verification

Use `php artisan media:verify-responsive-images` separately for read-only
checks. It reports aggregate results without exposing stored paths and does not
modify media.

Production also runs `media:verify-responsive-images` daily at 05:00 and emails
its aggregate output only when verification fails. Treat that notification as
media-integrity degradation and arrange an approved repair or restore. Existing
media damage is not automatically a release failure; releases that change media
behavior still require targeted media verification.

Failed derivative generation during an admin upload leaves the original upload
and any previously valid derivatives available, and writes a path-free warning
identifying the appropriate retry command. Do not add stored media paths to that
log context.

## Generated post artwork

`php artisan posts:generate-images` generates artwork (under
`featured-images/`) for posts without a featured image. `--force` also
regenerates earlier generated artwork but never replaces an uploaded image.

## Oversized images

`php artisan media:optimize-images` skips an image larger than the 20 megapixel
cap with a warning (see [Media architecture](../architecture/media.md#upload-limits)).

## Reviewing orphaned media

`php artisan media:find-orphans` only reports candidates. It runs weekly on
production (see [Scheduled tasks](scheduled-tasks.md#production-only-scheduled-tasks)).

Its optional `--delete` mode is destructive and requires operator approval.

- It deletes only unreferenced files in managed media directories after a
  24-hour grace period.
- It reads record and embedded-content references again once, immediately
  before the delete phase, so a file that content started to use during the scan
  is kept. The reference queries run twice in total, however many orphans there
  are.
- Attachments referenced by Markdown and SEO images are retained.
- Unknown directories and recent uploads are retained for review; they are not
  automatically safe to delete.

A nonzero result can therefore mean retained candidates or missing referenced
files, not just a failed storage operation. Without `--delete`, which is how the
scheduler runs it, the command exits nonzero whenever any orphaned file or
missing referenced file exists. The weekly run therefore emails a failure for as
long as one orphan remains, until it is reviewed and removed.

This sits uneasily with the
[failed-job retention rule](scheduled-tasks.md#failed-job-retention) not to
schedule a command that fails merely because retained records exist; the owner
has not yet decided which behavior to keep.

Take a recoverable backup before any approved cleanup.

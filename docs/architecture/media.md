# Media

Uploaded images are validated and stored through Laravel's `public` filesystem
disk. Commands for setting up, repairing and checking media are in
[Media operations](../operations/media.md). Design guidance for artwork is in
[Editorial design](../editorial-design.md) and
[Project art direction](../project-art-direction.md).

## Storage

Uploaded images and audio are validated and stored through the `public` disk.
Models store explicit file paths and never touch the disk themselves; their
observers hand file changes to one lifecycle service (see [Cleanup](#cleanup)).
Every remaining upload is an image; episode audio uploads are retired (see
[Publishing and content](publishing-and-content.md#podcasts-and-episodes)).

## Responsive variants

Project and post featured images and podcast cover images retain their original
upload as the canonical fallback and generate WebP variants for responsive
public rendering at the widths listed in `config/media.php` (`responsive_widths`,
640px and 1280px by default). Replacing or deleting an uploaded image also
removes its variants. Variants are generated synchronously, in the saving
request, once its database transaction commits (queueing them is a possible
follow-up that depends on the production server's capacity).

Episode artwork keeps only its original upload: episode pages do not render a
`srcset`, so episodes have no responsive variants by design.

Bundled podcast cover fallbacks provide 128px, 320px, and 512px Vite-managed
variants for smaller episode artwork. The presenters in `app/Presenters` choose
between an upload and its bundled fallback and build each `srcset` (see
[Public site and SEO](public-site-and-seo.md#pages-and-viewmodels)).

Posts without a featured image can get generated artwork under
`featured-images/` (see
[Generated post artwork](../operations/media.md#generated-post-artwork)).

## Upload limits

- Livewire's temporary-upload limit matches the 10 MB image field limit
  (`ImageUploadOptimizer::MAX_FILE_SIZE_KB`).
- Images are also capped at 20 megapixels (`ImageUploadOptimizer::MAX_PIXELS`),
  read from the image header before anything is decoded. GD needs about 4 bytes
  per pixel, so a 20 megapixel image peaks at about 90 MB while optimizing
  (under PHP's common 128 MB memory limit; 40 megapixels would need about
  170 MB), and a small file declaring far more pixels could exhaust the shared
  server's memory.
- The admin upload field rejects a larger image with a validation error, and the
  optimizer refuses to decode one (`media:optimize-images` skips it with a
  warning).

## Cleanup

`App\Services\StoredMediaLifecycle` owns every stored-media file change. The
post, project, podcast and episode observers call it for their media attribute,
passing a variant label only when that attribute has responsive variants:

| Model event | What happens after the commit |
| --- | --- |
| `created` | Generate the new image's variants. |
| `updated` (media path changed) | Delete the previous original and its variants, then generate the new image's variants. |
| `forceDeleted` | Delete the original and its variants. |
| `deleted` (trash), `restored` | Nothing: files stay so a restore is complete. |

Every change waits for a successful database commit, so a rolled back save or
delete leaves all files in place. That includes post OG caches (cleared by
`PostObserver`) and media from cascaded episode deletions, which commit with
their podcast. Only a force delete removes owned files (see
[Publishing and content](publishing-and-content.md#trash-and-permanent-deletion)).
Models never touch the disk; an architecture test keeps `Storage`, `Mail` and
`Http` out of observers and `App\Services` out of models.

Files that no records reference are found by the orphan report (see
[Reviewing orphaned media](../operations/media.md#reviewing-orphaned-media)).

## Generated OG images

Generated post OG images are cached on the private local filesystem. Cache
validity is based on the rendered title, category name, and an explicit renderer
version; deleting a post removes its cached image.

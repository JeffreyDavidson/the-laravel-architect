# Media

Uploaded images are validated and stored through Laravel's `public` filesystem
disk. Commands for setting up, repairing and checking media are in
[Media operations](../operations/media.md). Design guidance for artwork is in
[Editorial design](../editorial-design.md) and
[Project art direction](../project-art-direction.md).

## Storage

Uploaded images and audio are validated and stored through the `public` disk.
Models store explicit file paths and remove replaced or record-owned files.
Every remaining upload is an image; episode audio uploads are retired (see
[Publishing and content](publishing-and-content.md#podcasts-and-episodes)).

## Responsive variants

Project and post featured images and podcast cover images retain their original
upload as the canonical fallback and generate WebP variants for responsive
public rendering at the widths listed in `config/media.php` (`responsive_widths`,
640px and 1280px by default). Replacing or deleting an uploaded image also
removes its variants.

Bundled podcast cover fallbacks provide 128px, 320px, and 512px Vite-managed
variants for smaller episode artwork.

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

Media removal waits for a successful database commit, including responsive
variants, post OG caches, and media from cascaded episode deletions. A soft
delete keeps media so a restore is complete; only a force delete removes owned
files (see [Publishing and content](publishing-and-content.md#trash-and-permanent-deletion)).

Files that no records reference are found by the orphan report (see
[Reviewing orphaned media](../operations/media.md#reviewing-orphaned-media)).

## Generated OG images

Generated post OG images are cached on the private local filesystem. Cache
validity is based on the rendered title, category name, and an explicit renderer
version; deleting a post removes its cached image.

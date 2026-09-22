# Brand assets

The original elephant-and-coffee badge remains the primary identity. Its files
(`logo-color-black-bg.png` and `logo-color-128.webp`) are retained, and the footer
continues to use the original badge.

The header and browser icon use the companion elephant head. The full-size
  `docs/assets/elephant-companion.png` is a transparent raster adaptation created
with the built-in image-generation tool, using the original logo as reference.
It is not a pixel-exact extraction or a vector master.

The generation brief was to isolate the original elephant's head, ears, eyes,
tusks, and curled trunk, preserve its expression and blue palette, and remove
the cup, torso, shield, and lettering with minimal contour reconstruction.

  The 128px WebP serves the header; the 16px and 32px PNGs serve the favicon; the
  180px PNG serves the Apple touch icon. Existing manifest icons retain the original
  badge.
The header retains the existing Empera and IBM Plex typefaces.

## Placement and maintenance

Use the companion head for the public header, Filament/admin branding, and
favicon family. Use the original elephant-and-coffee badge where the full brand
lockup is appropriate, such as the footer and existing manifest icons. Keep
transparent edges intact and preserve the source aspect ratio; do not redraw or
stretch either mark to fit a container.

| Surface | Asset treatment | Review point |
| --- | --- | --- |
| Browser favicon | 16px and 32px companion PNGs | Must remain recognizable at native size |
| Apple touch icon | 180px companion PNG | Check the mark against the system mask/background |
| Public/admin header | 128px companion WebP | Preserve transparent breathing room and contrast in both themes |
| Footer/manifest | Original badge variants | Keep the full lockup legible; do not substitute the head |
| Filament project image | Project-specific artwork | Follow [project art direction](project-art-direction.md), not this identity-asset guide |

When replacing an identity asset, verify every consuming surface together:
browser tab, mobile/home-screen icon, public header, admin panel, login page,
footer, and error pages. Update the source note and any generated variants in
the same change. Do not commit a private source image, embedded credentials,
or an undocumented third-party asset.

# Brand assets

The original elephant-and-coffee badge remains the primary identity. Its files
(`logo-color-black-bg.png` and `logo-color-128.webp`) are retained, and existing
manifest icons continue to use the original badge.

The header, footer, and admin logo use the detailed companion elephant head. The full-size
`docs/assets/elephant-companion.png` is a transparent raster adaptation created
with the built-in image-generation tool, using the original logo as reference.
It is not a pixel-exact extraction or a vector master.

The generation brief was to isolate the original elephant's head, ears, eyes,
tusks, and curled trunk, preserve its expression and blue palette, and remove
the cup, torso, shield, and lettering with minimal contour reconstruction.

The 128px companion WebP serves the header, footer, and admin logo; the 180px
companion PNG serves the Apple touch icon. Existing manifest icons retain the
original badge.

The simplified small elephant serves the public site, error pages, and admin
browser tabs: `public/images/elephant-small-16.png` and
`public/images/elephant-small-32.png`. Both are transparent exports of the accepted
fitted vector in the private meta repository at
`~/Projects/the-laravel-architect-meta/brand/small-head-v3/variants/fitted.svg`.
`public/favicon.ico` contains the same artwork at 16px and 32px for legacy fallback.
The small head has solid white tusks and transparent eyes; it does not replace
the detailed head on larger surfaces. The old companion 16px/32px and badge
16px/32px PNGs remain for reference, but no templates link them.

The header and footer place “The Laravel” in small uppercase IBM Plex Mono above
“Architect” in Empera, with matching colours and letter-spacing. The footer uses
a smaller 40px head and 20px wordmark; the header uses 52px and 24px respectively.
Both show the uncropped companion head.

## Placement and maintenance

Use the detailed companion head for the public header and footer, Filament/admin
branding, and Apple touch icon. Use the simplified small head for browser favicons.
Use the original elephant-and-coffee badge where the full brand lockup is
appropriate, such as existing manifest icons. Keep
transparent edges intact and preserve the source aspect ratio; do not redraw or
stretch either mark to fit a container.

| Surface | Asset treatment | Review point |
| --- | --- | --- |
| Public/error/admin browser favicon | Public: 16px and 32px elephant-small PNGs; error/admin: 32px; favicon.ico fallback: 16px/32px | Must remain recognizable at native size |
| Apple touch icon | 180px companion PNG | Check the mark against the system mask/background |
| Public/admin header | 128px companion WebP | Preserve transparent breathing room and contrast in both themes |
| Footer | 128px companion WebP displayed at 40px, with the header's two-line name arrangement | Preserve the uncropped head and contrast in both themes |
| Manifest | Original badge variants | Keep the full lockup legible; do not substitute the head |
| Filament project image | Project-specific artwork | Follow [project art direction](project-art-direction.md), not this identity-asset guide |

When replacing an identity asset, verify every consuming surface together:
browser tab, mobile/home-screen icon, public header, admin panel, login page,
footer, and error pages. Update the source note and any generated variants in
the same change. Do not commit a private source image, embedded credentials,
or an undocumented third-party asset.

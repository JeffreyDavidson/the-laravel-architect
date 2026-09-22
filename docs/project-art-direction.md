# Project art direction

Use this guide whenever a project needs a new or refreshed portfolio image.
It extends [DESIGN.md](../DESIGN.md) and [the brand-asset guide](brand-assets.md).
The aim is to show what the project actually is, inside the site's consistent
presentation. One reusable brief per image is enough; a project does not need
its own permanent design document unless it has a separate design system.

## Choose the image

| Project situation | Preferred image |
| --- | --- |
| Working website or application | Current screenshot showing the distinctive interface |
| Redesign or portfolio refresh | Screenshot of the current version; identify older views in the write-up |
| Useful interaction spanning several screens | One strong featured screenshot plus inline detail images |
| Backend tool, package, or system with no useful UI | Clear technical diagram or explicitly conceptual illustration |
| Product still being designed | Actual prototype, clearly identified as a prototype |
| No suitable image yet | Existing “Project preview coming soon” placeholder |

The current Filament form explicitly asks for a real product screenshot with
its original colors. Use that as the default for shipped products. Generated
illustrations can explain a project with no meaningful screenshot, but should
not imply a fabricated interface is the shipped product. Describe conceptual
artwork in the surrounding project copy.

For The Laravel Architect's own project record, capture the current homepage.
Include Jeffrey's portrait when it is part of the actual page or the approved
composition calls for it; do not add a separate portrait to every project image.

## Canvas, frame, and crop

Prepare a **1600 × 900** landscape image, or a larger 16:9 master for archiving.
This follows the project form's recommendation. The site displays the image in
a 16:9 viewport using `object-contain`: it preserves the whole image rather than
cropping it. Other ratios may leave unused space, so prepare the intended canvas
before upload when possible. Do not stretch a square or portrait source.

The shared artwork component adds:

- A header containing the project title and The Laravel Architect name.
- Theme-aware blue/graphite framing, a fine border, and rounded corners.
- Inner padding and a rounded image viewport.
- A matching placeholder when there is no image.

Upload the product image itself. Avoid baking in another TLA title bar, border,
rounded frame, drop shadow, or logo watermark; the site already supplies those.
Keep the product's own brand colors and logo intact. A portfolio of different
products should not make every product's interface look like this website.

Inspect the image at the smaller project-card size as well as the detail-page
size. The rendering hints target approximately 560px-wide desktop cards and
1152px-wide detail artwork. Tiny interface text does not need to carry the story;
put important explanations and outcomes in the write-up.

## Capture a useful screenshot

1. Open the correct product version and a representative screen with intentional
   content. For a website, start with the homepage or the page that best explains it.
2. Dismiss unrelated menus, cookie notices, browser chrome, and development tools.
   Include an open menu only when it demonstrates the feature being discussed.
3. Use approved public or demonstration data. Remove credentials, private names,
   emails, notifications, browser tabs, account details, and internal URLs.
4. Choose a stable viewport and allow fonts and images to finish loading. Capture
   an intentional screen area rather than shrinking a full-page screenshot until
   nothing is recognizable.
5. Export a sharp source, retaining its original colors and proportions. Prefer
   a direct capture over repeated JPEG compression or enlarging a small image.
6. Review it in the site's actual project frame in light and dark modes.

If several screens matter, use the strongest one as the featured image. Put the
others in the Full Write-up with meaningful alt text and paragraph captions.
The write-up should explain **The problem**, **My contribution**, and **The result**,
using verified outcomes and identifying work that is still in development.

## Illustration and diagram direction

For a project without a useful interface, build the composition around one
specific concept: a dependency graph, a workflow, a physical analogy, or a
recognizable artifact. Use the global blue/graphite palette where the project has
no identity of its own. Limit secondary accents and keep the subject legible at
card size. Technical diagrams should use editable text and shapes when precise
labels matter; generated imagery suits conceptual subjects and textures.

Avoid fake testimonials, metrics, partner logos, interface states, and tiny
invented code. Prefer a clean image with an explanation outside it. If using
an existing screenshot as a reference, preserve its actual layout and content;
an illustration should not silently replace it with an imagined product.

## Upload and storage behavior

Use **Projects → Links & Media** in Filament to update `featured_image_path`.
The original source is worth retaining outside the deployment for later edits;
the uploaded copy is optimized automatically:

| Property | Current implementation |
| --- | --- |
| Upload limit | 10 MB |
| Stored format | WebP, quality 82 |
| Maximum stored dimensions | 1600px per side, preserving aspect ratio |
| Storage | Public disk, `projects` directory |
| Stored filename | Generated ULID with `.webp` extension |
| Responsive widths | 640 and 1280px when the source is wide enough |
| Variant location | Sibling `responsive` directory, with width suffixes |
| Display | Original as fallback; WebP `srcset` when variants exist |

The project observer generates variants when a record is created or its image
path changes. Sources are not upscaled to satisfy a variant width. Replacing
bytes at an unchanged storage path does not follow the same changed-path flow;
prefer the normal upload/save process. Use [the media operations procedure](operations.md)
for verification or repair rather than manually naming and copying derivatives.

For local working files, use descriptive names such as
`the-laravel-architect-homepage-2026-09-master.png` and
`the-laravel-architect-homepage-1600x900.webp`. These are working-file conventions;
Filament does not preserve those names as storage paths.

Staging and production have separate content and storage. Updating one record
does not establish that the other environment has the same image. Record which
environment was changed and verify its public project page after saving. Follow
the existing operations approval process for production changes.

The current featured-image alt text is generated as “[project title] project
preview”; the form has no dedicated alt-text field. Keep essential information
in the description and write-up. Use descriptive alt text for meaningful inline
images, especially diagrams.

## Reusable brief

```text
Project title:
What it does and who it helps:
Current status: shipped / in development / prototype
Page or feature to show:
Image type: screenshot / diagram / conceptual illustration
Source URL or approved reference asset:
Product branding that must remain accurate:
Private content to remove:
Focal point:
Canvas: 16:9; preferred upload 1600 × 900
Text or UI that must remain legible:
Supporting inline screenshots needed:
Caption and image description:
Working filename:
Destination record and environment:
```

For conceptual imagery, use this prompt structure:

> Create a conceptual illustration for [project], which helps [audience] do
> [task]. Depict [specific concept] with [visual approach]. Use [actual product
> palette, or the site's blue/graphite palette]. Compose on a 16:9 canvas with
> one clear focal point that reads at thumbnail size. Leave framing and titles
> to the website. Preserve [reference details]; exclude [unwanted elements].

## Acceptance check

- The image accurately represents the named project and its current status.
- The screen or concept is recognizable at both card and detail sizes.
- No private data, accidental browser UI, stretched content, or duplicate frame remains.
- Original product colors and proportions survive the WebP conversion.
- Both themes present the image cleanly, including any unused contain-space.
- The saved record and responsive variants load in the intended environment.
- The description and write-up explain what the image alone cannot communicate.

## Implementation references

- [Project form and screenshot guidance](../app/Filament/Resources/Projects/Schemas/ProjectForm.php)
- [Shared project frame and image sizing](../resources/views/components/projects/artwork.blade.php)
- [Project detail layout](../resources/views/pages/projects/show.blade.php)
- [Image upload optimizer](../app/Services/ImageUploadOptimizer.php)
- [Responsive image widths and quality](../app/Services/ResponsiveImageVariants.php)
- [Project observer](../app/Observers/ProjectObserver.php)
- [Image lifecycle](../app/Services/ResponsiveImageLifecycle.php)

For article illustrations and their different crop requirements, use
[editorial design](editorial-design.md).
